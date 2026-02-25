<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\CaseFile;
use App\Models\Client;
use App\Models\Setting;
use App\Models\User;
use App\Services\TrafficLightService;

class CaseController
{
    public function index(): void
    {
        $page = max((int)($_GET['page'] ?? 1), 1);
        $filters = [
            'client_id' => $_GET['client_id'] ?? null,
            'responsable_user_id' => $_GET['responsable_user_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'q' => trim($_GET['q'] ?? ''),
        ];
        $res = CaseFile::paginated($filters, $page);
        if (($_GET['export'] ?? '') === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=expedientes.csv');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Folio','Cliente','Asunto','Responsable','Vencimiento','Estado']);
            foreach (CaseFile::allWithRelations() as $row) {
                fputcsv($out, [$row['folio'],$row['client_name'],$row['asunto_tipo'],$row['responsable_name'],$row['fecha_vencimiento'],$row['status']]);
            }
            fclose($out);
            exit;
        }
        $settings = Setting::get();
        foreach ($res['data'] as &$case) {
            $case['calc'] = TrafficLightService::calculate($case, (int)$settings['warning_days']);
        }

        View::render('pages/cases', [
            'title' => 'Expedientes',
            'cases' => $res['data'],
            'total' => $res['total'],
            'page' => $page,
            'clients' => Client::active(),
            'lawyers' => User::lawyers(),
            'filters' => $filters,
            'warningDays' => (int)$settings['warning_days'],
        ]);
    }

    public function createForm(): void
    {
        View::render('pages/case_form', [
            'title' => 'Nuevo expediente',
            'clients' => Client::active(),
            'lawyers' => User::lawyers(),
            'case' => null,
            'history' => [],
        ]);
    }

    public function editForm(): void
    {
        $case = CaseFile::find((int)($_GET['id'] ?? 0));
        if (!$case) {
            redirect('/cases');
        }
        View::render('pages/case_form', [
            'title' => 'Editar expediente',
            'clients' => Client::active(),
            'lawyers' => User::lawyers(),
            'case' => $case,
            'history' => CaseFile::history((int)$case['id']),
        ]);
    }

    public function store(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        $settings = Setting::get();
        $draft = [
            'id' => 0,
            'status' => 'Abierto',
            'fecha_termino_real' => $_POST['fecha_termino_real'] ?: null,
            'fecha_vencimiento' => $_POST['fecha_vencimiento'],
        ];
        $calc = TrafficLightService::calculate($draft, (int)$settings['warning_days']);

        $id = CaseFile::create([
            'folio' => CaseFile::nextFolio(),
            'client_id' => (int)$_POST['client_id'],
            'asunto_tipo' => $_POST['asunto_tipo'],
            'descripcion' => trim($_POST['descripcion']),
            'responsable_user_id' => (int)$_POST['responsable_user_id'],
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_vencimiento' => $_POST['fecha_vencimiento'],
            'fecha_termino_real' => $_POST['fecha_termino_real'] ?: null,
            'porcentaje_avance' => (int)$_POST['porcentaje_avance'],
            'notas' => trim($_POST['notas']),
            'status' => $calc['status'],
            'last_traffic_light' => $calc['traffic_light'],
        ]);
        CaseFile::addHistory($id, (int)Auth::user()['id'], (int)$_POST['porcentaje_avance'], trim($_POST['comentario'] ?? 'Alta de expediente'));
        AuditLog::add((int)Auth::user()['id'], 'cases', $id, 'create', $_POST, $_SERVER['REMOTE_ADDR'] ?? '-');
        redirect('/cases');
    }

    public function update(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        $id = (int)$_POST['id'];
        $existing = CaseFile::find($id);
        if (!$existing) {
            redirect('/cases');
        }

        $settings = Setting::get();
        $draft = [
            'status' => $_POST['cerrar'] ?? '' ? 'Cerrado' : 'Abierto',
            'fecha_termino_real' => $_POST['fecha_termino_real'] ?: null,
            'fecha_vencimiento' => $_POST['fecha_vencimiento'],
        ];
        $calc = TrafficLightService::calculate($draft, (int)$settings['warning_days']);

        CaseFile::update($id, [
            'client_id' => (int)$_POST['client_id'],
            'asunto_tipo' => $_POST['asunto_tipo'],
            'descripcion' => trim($_POST['descripcion']),
            'responsable_user_id' => (int)$_POST['responsable_user_id'],
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_vencimiento' => $_POST['fecha_vencimiento'],
            'fecha_termino_real' => $_POST['fecha_termino_real'] ?: null,
            'porcentaje_avance' => (int)$_POST['porcentaje_avance'],
            'notas' => trim($_POST['notas']),
            'status' => $calc['status'],
            'last_traffic_light' => $calc['traffic_light'],
        ]);
        CaseFile::addHistory($id, (int)Auth::user()['id'], (int)$_POST['porcentaje_avance'], trim($_POST['comentario'] ?? 'Actualización'));
        AuditLog::add((int)Auth::user()['id'], 'cases', $id, 'update', ['before' => $existing, 'after' => $_POST], $_SERVER['REMOTE_ADDR'] ?? '-');
        redirect('/cases');
    }
}
