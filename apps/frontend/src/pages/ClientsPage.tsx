import { Box, Button, Stack, TextField } from '@mui/material';
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import { useEffect, useMemo, useState } from 'react';
import api from '../api/client';
import { exportCsv, exportExcel } from '../utils/export';

export function ClientsPage() {
  const [rows, setRows] = useState<any[]>([]);
  const [q, setQ] = useState('');
  useEffect(() => { api.get('/clients').then((r) => setRows(r.data)); }, []);
  const filtered = useMemo(() => rows.filter((x) => x.name.toLowerCase().includes(q.toLowerCase())), [rows, q]);
  const columns: GridColDef[] = [
    { field: 'name', headerName: 'Cliente', flex: 1 },
    { field: 'businessType', headerName: 'Tipo', width: 150 },
    { field: 'email', headerName: 'Correo', width: 220 },
    { field: 'phone', headerName: 'Teléfono', width: 160 }
  ];

  return <Box><Stack direction="row" spacing={1} mb={1}><TextField size="small" label="Buscar" value={q} onChange={(e) => setQ(e.target.value)} /><Button onClick={() => exportExcel(filtered, 'clientes')}>Excel</Button><Button onClick={() => exportCsv(filtered, 'clientes')}>CSV</Button></Stack><div style={{ height: 580 }}><DataGrid rows={filtered} columns={columns} /></div></Box>;
}
