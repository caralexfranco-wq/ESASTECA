import { Card, CardContent, Grid2, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import api from '../api/client';

export function DashboardPage() {
  const [data, setData] = useState<any>();
  useEffect(() => { api.get('/dashboard').then((r) => setData(r.data)); }, []);
  const cards = [
    ['Abiertos', data?.cards?.abiertos ?? 0],
    ['En riesgo', data?.cards?.riesgo ?? 0],
    ['Vencidos', data?.cards?.vencidos ?? 0],
    ['Cerrados', data?.cards?.cerrados ?? 0]
  ];

  return <Grid2 container spacing={2}>{cards.map(([title, value]) => <Grid2 size={{ xs: 12, sm: 6, md: 3 }} key={String(title)}><Card><CardContent><Typography color="text.secondary">{title}</Typography><Typography variant="h4">{value}</Typography></CardContent></Card></Grid2>)}</Grid2>;
}
