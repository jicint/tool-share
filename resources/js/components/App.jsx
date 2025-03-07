import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from '../contexts/AuthContext';
import Login from './Login';
import AddTool from './tools/AddTool';
import ToolList from './tools/ToolList';
import RentTool from './tools/RentTool';
import RentalList from './rentals/RentalList';
import Dashboard from './dashboard/Dashboard';
import CreateToolForm from './tools/CreateToolForm';

const App = () => {
    return (
        <AuthProvider>
            <Router>
                <Routes>
                    <Route path="/login" element={<Login />} />
                    <Route path="/tools/add" element={<AddTool />} />
                    <Route path="/tools" element={<ToolList />} />
                    <Route path="/tools/:id/rent" element={<RentTool />} />
                    <Route path="/rentals" element={<RentalList />} />
                    <Route path="/dashboard" element={<Dashboard />} />
                    <Route path="/tools/create" element={<CreateToolForm />} />
                </Routes>
            </Router>
        </AuthProvider>
    );
};

export default App; 