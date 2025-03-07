import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from '../../contexts/AuthContext';
import { Link } from 'react-router-dom';

const RentalList = () => {
    const [rentals, setRentals] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const { token } = useAuth();

    useEffect(() => {
        loadRentals();
    }, []);

    const loadRentals = async () => {
        try {
            const response = await axios.get('/api/rentals', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            setRentals(response.data);
            setLoading(false);
        } catch (error) {
            setError('Failed to load rentals');
            setLoading(false);
        }
    };

    if (loading) return <div className="container mt-5">Loading...</div>;
    if (error) return <div className="container mt-5 alert alert-danger">{error}</div>;

    return (
        <div className="container mt-5">
            <h2 className="mb-4">My Rentals</h2>
            
            {rentals.length === 0 ? (
                <div className="alert alert-info">
                    You haven't rented any tools yet. 
                    <Link to="/tools" className="alert-link ms-2">Browse available tools</Link>
                </div>
            ) : (
                <div className="row">
                    {rentals.map(rental => (
                        <div key={rental.id} className="col-md-6 mb-4">
                            <div className="card">
                                <div className="card-body">
                                    <h5 className="card-title">{rental.tool.name}</h5>
                                    <div className="card-text">
                                        <div className="mb-2">
                                            <strong>Rental Period:</strong><br />
                                            From: {new Date(rental.start_date).toLocaleDateString()}<br />
                                            To: {new Date(rental.end_date).toLocaleDateString()}
                                        </div>
                                        <div className="mb-2">
                                            <strong>Total Price:</strong> ${rental.total_price}
                                        </div>
                                        <div className="mb-2">
                                            <strong>Status:</strong>
                                            <span className={`badge ms-2 ${
                                                rental.status === 'active' ? 'bg-success' :
                                                rental.status === 'completed' ? 'bg-secondary' :
                                                'bg-warning'
                                            }`}>
                                                {rental.status.charAt(0).toUpperCase() + rental.status.slice(1)}
                                            </span>
                                        </div>
                                        <div className="mb-2">
                                            <strong>Owner:</strong> {rental.tool.user.name}
                                        </div>
                                    </div>
                                    {rental.status === 'active' && (
                                        <button 
                                            className="btn btn-primary"
                                            onClick={() => handleReturn(rental.id)}
                                        >
                                            Return Tool
                                        </button>
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

export default RentalList; 