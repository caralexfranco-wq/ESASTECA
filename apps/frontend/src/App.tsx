import { Navigate, Route, Routes } from 'react-router-dom';
import { useAuth } from './auth/AuthContext';
import { AppLayout } from './layout/AppLayout';
import { LoginPage } from './pages/LoginPage';
import { DashboardPage } from './pages/DashboardPage';
import { ClientsPage } from './pages/ClientsPage';
import { CasesPage } from './pages/CasesPage';
import { UsersPage } from './pages/UsersPage';

export function App() {
  const { user } = useAuth();

  if (!user) return <LoginPage />;

  return (
    <Routes>
      <Route path="/" element={<AppLayout />}>
        <Route index element={<DashboardPage />} />
        <Route path="clientes" element={<ClientsPage />} />
        <Route path="expedientes" element={<CasesPage />} />
        <Route path="semaforo" element={<CasesPage />} />
        <Route path="usuarios" element={<UsersPage />} />
      </Route>
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
