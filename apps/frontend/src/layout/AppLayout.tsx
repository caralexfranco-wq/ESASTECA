import { AppBar, Box, Button, Drawer, List, ListItemButton, ListItemText, Toolbar, Typography } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import SearchIcon from '@mui/icons-material/Search';
import FileDownloadIcon from '@mui/icons-material/FileDownload';
import { Link, Outlet, useLocation } from 'react-router-dom';
import { useAuth } from '../auth/AuthContext';

const menu = [
  { path: '/', label: 'Inicio' },
  { path: '/clientes', label: 'Clientes' },
  { path: '/expedientes', label: 'Expedientes o casos' },
  { path: '/semaforo', label: 'Mostrar semáforo' },
  { path: '/usuarios', label: 'Usuarios' }
];

export function AppLayout() {
  const { logout, user } = useAuth();
  const loc = useLocation();
  return (
    <Box sx={{ display: 'flex' }}>
      <Drawer variant="permanent" sx={{ width: 230, [`& .MuiDrawer-paper`]: { width: 230, boxSizing: 'border-box' } }}>
        <Toolbar><Typography fontWeight={700}>ESA STECA Legal</Typography></Toolbar>
        <List>
          {menu.map((m) => (
            <ListItemButton key={m.path} selected={loc.pathname === m.path} component={Link} to={m.path}>
              <ListItemText primary={m.label} />
            </ListItemButton>
          ))}
          <ListItemButton onClick={logout}><ListItemText primary="Salir" /></ListItemButton>
        </List>
      </Drawer>
      <Box sx={{ flexGrow: 1, ml: '230px' }}>
        <AppBar position="static" color="default" elevation={1}>
          <Toolbar sx={{ gap: 1 }}>
            <Button variant="contained" startIcon={<AddIcon />}>Nuevo</Button>
            <Button variant="outlined" startIcon={<EditIcon />}>Editar</Button>
            <Button variant="outlined" startIcon={<FileDownloadIcon />}>Exportar</Button>
            <Button variant="outlined" startIcon={<SearchIcon />}>Buscar</Button>
            <Box sx={{ ml: 'auto' }}><Typography variant="body2">{user?.name} ({user?.role})</Typography></Box>
          </Toolbar>
        </AppBar>
        <Box sx={{ p: 2 }}><Outlet /></Box>
      </Box>
    </Box>
  );
}
