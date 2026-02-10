import { Box, Button, Card, CardContent, Stack, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { useAuth } from '../auth/AuthContext';

export function LoginPage() {
  const { login } = useAuth();
  const [email, setEmail] = useState('admin@local');
  const [password, setPassword] = useState('Password123!');

  return <Box sx={{ minHeight: '100vh', display: 'grid', placeItems: 'center' }}><Card sx={{ width: 380 }}><CardContent><Typography variant="h5" mb={2}>ESA STECA Legal</Typography><Stack spacing={2}><TextField label="Correo" value={email} onChange={(e) => setEmail(e.target.value)} /><TextField type="password" label="Password" value={password} onChange={(e) => setPassword(e.target.value)} /><Button variant="contained" onClick={() => login(email, password)}>Entrar</Button></Stack></CardContent></Card></Box>;
}
