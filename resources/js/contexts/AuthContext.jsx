import React, { createContext, useContext, useState } from 'react';
import axios from 'axios';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const login = async (email, password) => {
        setLoading(true);
        setError(null);
        try {
            const response = await axios.post('/login', {
                email,
                password
            });
            setUser(response.data.user);
            return response.data;
        } catch (err) {
            setError(err.response?.data?.message || 'Login failed');
            throw err;
        } finally {
            setLoading(false);
        }
    };

    const logout = async () => {
        try {
            await axios.post('/logout');
            setUser(null);
        } catch (err) {
            console.error('Logout failed:', err);
        }
    };

    const value = {
        user,
        loading,
        error,
        login,
        logout
    };

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
}; 