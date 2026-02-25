<?php

namespace App\Models;

class CaseFile extends BaseModel
{
    public static function nextFolio(): string
    {
        $year = date('Y');
        $stmt = self::db()->prepare('SELECT COUNT(*) c FROM cases WHERE YEAR(created_at)=?');
        $stmt->execute([$year]);
        $seq = (int)$stmt->fetch()['c'] + 1;
        return sprintf('EXP-%s-%06d', $year, $seq);
    }

    public static function paginated(array $filters, int $page = 1, int $perPage = 15): array
    {
        $where = ['1=1'];
        $params = [];
        foreach (['client_id','responsable_user_id','status'] as $f) {
            if (!empty($filters[$f])) {
                $where[] = "c.$f = ?";
                $params[] = $filters[$f];
            }
        }
        if (!empty($filters['q'])) {
            $where[] = '(c.folio LIKE ? OR c.descripcion LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        $sqlWhere = implode(' AND ', $where);

        $countStmt = self::db()->prepare("SELECT COUNT(*) c FROM cases c WHERE $sqlWhere");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['c'];

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT c.*, cl.razon_social as client_name, u.name as responsable_name
                FROM cases c
                JOIN clients cl ON cl.id=c.client_id
                JOIN users u ON u.id=c.responsable_user_id
                WHERE $sqlWhere
                ORDER BY c.fecha_vencimiento ASC
                LIMIT $perPage OFFSET $offset";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);

        return ['data' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM cases WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function allWithRelations(): array
    {
        return self::db()->query('SELECT c.*, cl.razon_social as client_name, u.name as responsable_name FROM cases c JOIN clients cl ON cl.id=c.client_id JOIN users u ON u.id=c.responsable_user_id ORDER BY c.fecha_vencimiento')->fetchAll();
    }

    public static function create(array $d): int
    {
        $stmt = self::db()->prepare('INSERT INTO cases (folio,client_id,asunto_tipo,descripcion,responsable_user_id,fecha_inicio,fecha_vencimiento,fecha_termino_real,porcentaje_avance,notas,status,last_traffic_light,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
        $stmt->execute([$d['folio'],$d['client_id'],$d['asunto_tipo'],$d['descripcion'],$d['responsable_user_id'],$d['fecha_inicio'],$d['fecha_vencimiento'],$d['fecha_termino_real'],$d['porcentaje_avance'],$d['notas'],$d['status'],$d['last_traffic_light']]);
        return (int)self::db()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        self::db()->prepare('UPDATE cases SET client_id=?, asunto_tipo=?, descripcion=?, responsable_user_id=?, fecha_inicio=?, fecha_vencimiento=?, fecha_termino_real=?, porcentaje_avance=?, notas=?, status=?, last_traffic_light=?, updated_at=NOW() WHERE id=?')
            ->execute([$d['client_id'],$d['asunto_tipo'],$d['descripcion'],$d['responsable_user_id'],$d['fecha_inicio'],$d['fecha_vencimiento'],$d['fecha_termino_real'],$d['porcentaje_avance'],$d['notas'],$d['status'],$d['last_traffic_light'],$id]);
    }

    public static function addHistory(int $caseId, int $actorId, int $avance, string $comentario): void
    {
        self::db()->prepare('INSERT INTO case_history (case_id,actor_user_id,porcentaje_avance,comentario,created_at) VALUES (?,?,?,?,NOW())')
            ->execute([$caseId,$actorId,$avance,$comentario]);
    }

    public static function history(int $caseId): array
    {
        $stmt = self::db()->prepare('SELECT h.*, u.name actor_name FROM case_history h JOIN users u ON u.id=h.actor_user_id WHERE h.case_id=? ORDER BY h.id DESC');
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }

    public static function dashboardCounts(): array
    {
        return self::db()->query("SELECT
            SUM(status='Abierto') abiertos,
            SUM(status='En Riesgo') riesgo,
            SUM(status='Vencido') vencidos,
            SUM(status='Cerrado') cerrados
            FROM cases")->fetch() ?: [];
    }

    public static function byResponsible(int $userId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM cases WHERE responsable_user_id=? ORDER BY fecha_vencimiento LIMIT 10');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
