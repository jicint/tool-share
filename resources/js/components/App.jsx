import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from '../contexts/AuthContext';
import Register from './auth/Register';
import Login from './auth/Login';
import Dashboard from './Dashboard';
import AddTool from './tools/AddTool';
import EditTool from './tools/EditTool';
import ToolList from './tools/ToolList';
import ProtectedRoute from './auth/ProtectedRoute';

const App = () => {
    return (
        <AuthProvider>
            <Router>
                <Routes>
                    <Route path="/" element={<Navigate to="/dashboard" />} />
                    <Route path="/login" element={<Login />} />
                    <Route path="/register" element={<Register />} />
                    <Route 
                        path="/dashboard" 
                        element={
                            <ProtectedRoute>
                                <Dashboard />
                            </ProtectedRoute>
                        } 
                    />
                    <Route 
                        path="/tools/add" 
                        element={
                            <ProtectedRoute>
                                <AddTool />
                            </ProtectedRoute>
                        } 
                    />
                    <Route 
                        path="/tools" 
                        element={
                            <ProtectedRoute>
                                <ToolList />
                            </ProtectedRoute>
                        } 
                    />
                    <Route path="/tools/edit/:id" element={<ProtectedRoute><EditTool /></ProtectedRoute>} />
                </Routes>
            </Router>
        </AuthProvider>
    );
};

export default App; 