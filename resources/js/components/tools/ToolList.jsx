import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';

const ToolList = () => {
    const [tools, setTools] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const { user, token } = useAuth();
    const navigate = useNavigate();

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }

        loadTools();
    }, [token]);

    const loadTools = async () => {
        try {
            const response = await axios.get('/api/tools', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            const otherUsersTools = response.data.filter(tool => tool.user_id !== user?.id);
            setTools(otherUsersTools);
            setLoading(false);
        } catch (error) {
            setError('Failed to load tools');
            setLoading(false);
            if (error.response?.status === 401) {
                navigate('/login');
            }
        }
    };

    if (loading) return <div className="container mt-5">Loading...</div>;
    if (error) return <div className="container mt-5 alert alert-danger">{error}</div>;

    return (
        <div className="container mt-5">
            <div className="d-flex justify-content-between align-items-center mb-4">
                <h2>Available Tools for Rent</h2>
                <Link to="/tools/add" className="btn btn-primary">Add New Tool</Link>
            </div>
            
            {tools.length === 0 ? (
                <div className="alert alert-info">No tools available for rent.</div>
            ) : (
                <div className="row">
                    {tools.map(tool => (
                        <div key={tool.id} className="col-md-4 mb-4">
                            <div className="card h-100">
                                <div className="card-body">
                                    <h5 className="card-title">{tool.name}</h5>
                                    <p className="card-text">{tool.description}</p>
                                    <ul className="list-unstyled">
                                        <li><strong>Category:</strong> {tool.category}</li>
                                        <li><strong>Daily Rate:</strong> ${tool.daily_rate}</li>
                                        <li><strong>Condition:</strong> {tool.condition}</li>
                                        <li>
                                            <strong>Status:</strong>
                                            <span className={`badge ${tool.availability_status ? 'bg-success' : 'bg-danger'} ms-2`}>
                                                {tool.availability_status ? 'Available' : 'Rented'}
                                            </span>
                                        </li>
                                    </ul>
                                    {tool.availability_status && (
                                        <Link 
                                            to={`/tools/${tool.id}/rent`} 
                                            className="btn btn-primary"
                                        >
                                            Rent Tool
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default ToolList; 