import { Box, Chip, MenuItem, Stack, TextField } from '@mui/material';
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import { useEffect, useMemo, useState } from 'react';
import api from '../api/client';

const colorMap: Record<string, 'success' | 'warning' | 'error' | 'default'> = { VERDE: 'success', AMARILLO: 'warning', ROJO: 'error', GRIS: 'default' };

export function CasesPage() {
  const [rows, setRows] = useState<any[]>([]);
  const [traffic, setTraffic] = useState('ALL');
  useEffect(() => { api.get('/semaphore').then((r) => setRows(r.data)); }, []);
  const filtered = useMemo(() => rows.filter((r) => traffic === 'ALL' || r.trafficLight === traffic), [rows, traffic]);
  const columns: GridColDef[] = [
    { field: 'folio', headerName: 'Folio', width: 150 },
    { field: 'type', headerName: 'Tipo', width: 180 },
    { field: 'shortDescription', headerName: 'Descripción', flex: 1 },
    { field: 'dueDate', headerName: 'Vencimiento', width: 140, valueFormatter: (v) => new Date(v).toLocaleDateString() },
    { field: 'daysRemaining', headerName: 'Días restantes', width: 130 },
    { field: 'trafficLight', headerName: 'Semáforo', width: 120, renderCell: ({ row }) => <Chip label={row.trafficLight} color={colorMap[row.trafficLight]} size="small" /> }
  ];

  return <Box><Stack direction="row" mb={1}><TextField select size="small" label="Semáforo" value={traffic} onChange={(e) => setTraffic(e.target.value)} sx={{ width: 200 }}><MenuItem value="ALL">Todos</MenuItem><MenuItem value="VERDE">Verde</MenuItem><MenuItem value="AMARILLO">Amarillo</MenuItem><MenuItem value="ROJO">Rojo</MenuItem><MenuItem value="GRIS">Gris</MenuItem></TextField></Stack><div style={{ height: 580 }}><DataGrid rows={filtered} columns={columns} /></div></Box>;
}
