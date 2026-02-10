import { Box } from '@mui/material';
import { DataGrid, GridColDef } from '@mui/x-data-grid';
import { useEffect, useState } from 'react';
import api from '../api/client';
import { useAuth } from '../auth/AuthContext';

export function UsersPage() {
  const [rows, setRows] = useState<any[]>([]);
  const { user } = useAuth();
  useEffect(() => { if (user?.role === 'ADMIN') api.get('/users').then((r) => setRows(r.data)); }, [user]);
  const columns: GridColDef[] = [
    { field: 'fullName', headerName: 'Nombre', flex: 1 },
    { field: 'email', headerName: 'Correo', width: 220 },
    { field: 'role', headerName: 'Rol', width: 130, valueGetter: (_v, row) => row.role.name },
    { field: 'isActive', headerName: 'Activo', width: 100 }
  ];
  return <Box>{user?.role !== 'ADMIN' ? 'Sin permisos' : <div style={{ height: 580 }}><DataGrid rows={rows} columns={columns} /></div>}</Box>;
}
