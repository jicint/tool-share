import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useAuth } from '../../contexts/AuthContext';
import { Link } from 'react-router-dom';
import ReactStars from 'react-rating-stars-component';
import LoadingSpinner from '../common/LoadingSpinner';
import Notification from '../common/Notification';
import ConfirmDialog from '../common/ConfirmDialog';

const Dashboard = () => {
    const [stats, setStats] = useState({
        totalRentals: 0,
        activeRentals: 0,
        recentRentals: [],
        totalSpent: 0
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const { token } = useAuth();

    // Add state for rating modal
    const [showRatingModal, setShowRatingModal] = useState(false);
    const [selectedRental, setSelectedRental] = useState(null);
    const [rating, setRating] = useState(5);
    const [review, setReview] = useState('');

    // Add state for rating statistics
    const [ratingStats, setRatingStats] = useState({
        averageRating: 0,
        totalRatings: 0,
        ratingDistribution: {
            5: 0, 4: 0, 3: 0, 2: 0, 1: 0
        }
    });

    const [notification, setNotification] = useState(null);
    const [confirmDialog, setConfirmDialog] = useState({ show: false, action: null });

    const loadDashboardData = async () => {
        try {
            const [dashboardResponse, ratingStatsResponse] = await Promise.all([
                axios.get('/api/dashboard', {
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                }),
                axios.get('/api/rentals/rating-stats', {
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                })
            ]);
            
            setStats(dashboardResponse.data);
            setRatingStats(ratingStatsResponse.data);
            setLoading(false);
        } catch (error) {
            console.error('Error loading dashboard:', error);
            setError('Failed to load dashboard data');
            setLoading(false);
        }
    };

    // Helper function to show notifications
    const showNotification = (message, type = 'success') => {
        setNotification({ message, type });
    };

    const handleReturnTool = async (rentalId) => {
        setConfirmDialog({
            show: true,
            title: 'Confirm Return',
            message: 'Are you sure you want to return this tool?',
            action: async () => {
                setLoading(true);
                try {
                    await axios.post(`/api/rentals/${rentalId}/return`, {}, {
                        headers: { 
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        }
                    });
                    await loadDashboardData();
                    showNotification('Tool returned successfully');
                } catch (error) {
                    console.error('Return error:', error);
                    showNotification(
                        'Failed to return tool: ' + (error.response?.data?.error || error.message),
                        'error'
                    );
                } finally {
                    setLoading(false);
                }
            }
        });
    };

    const handleRateRental = async (rentalId) => {
        setLoading(true);
        try {
            await axios.post(`/api/rentals/${rentalId}/rate`, {
                rating,
                review
            }, {
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            
            setShowRatingModal(false);
            setSelectedRental(null);
            setRating(5);
            setReview('');
            await loadDashboardData();
            showNotification('Rating submitted successfully');
        } catch (error) {
            console.error('Rating error:', error);
            showNotification(
                'Failed to submit rating: ' + (error.response?.data?.error || error.message),
                'error'
            );
        } finally {
            setLoading(false);
        }
    };

    // Initial load
    useEffect(() => {
        loadDashboardData();
    }, [token]);

    if (loading) return <div className="container mt-4">Loading dashboard...</div>;
    if (error) return <div className="container mt-4 alert alert-danger">{error}</div>;

    return (
        <div className="container mx-auto px-4 py-8">
            {/* Notification Component */}
            {notification && (
                <Notification
                    message={notification.message}
                    type={notification.type}
                    onClose={() => setNotification(null)}
                />
            )}

            {/* Confirmation Dialog */}
            <ConfirmDialog
                isOpen={confirmDialog.show}
                title={confirmDialog.title}
                message={confirmDialog.message}
                onConfirm={confirmDialog.action}
                onClose={() => setConfirmDialog({ show: false })}
            />

            {/* Loading Overlay */}
            {loading && (
                <div className="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
                    <LoadingSpinner size="large" />
                </div>
            )}

            <h2>Dashboard</h2>
            
            {/* Stats Cards */}
            <div className="row mt-4">
                <div className="col-md-4">
                    <div className="card">
                        <div className="card-body">
                            <h5 className="card-title">Total Rentals</h5>
                            <p className="card-text h2">{stats.totalRentals}</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card">
                        <div className="card-body">
                            <h5 className="card-title">Active Rentals</h5>
                            <p className="card-text h2">{stats.activeRentals}</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card">
                        <div className="card-body">
                            <h5 className="card-title">Total Spent</h5>
                            <p className="card-text h2">${stats.totalSpent || 0}</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-4">
                    <div className="card">
                        <div className="card-body">
                            <h5 className="card-title">Rating Statistics</h5>
                            <div className="d-flex align-items-center mb-2">
                                <ReactStars
                                    count={5}
                                    value={ratingStats.averageRating}
                                    edit={false}
                                    size={24}
                                    activeColor="#ffd700"
                                />
                                <span className="ms-2">({ratingStats.averageRating.toFixed(1)})</span>
                            </div>
                            <p className="card-text">Total Ratings: {ratingStats.totalRatings}</p>
                            
                            {/* Rating Distribution */}
                            <div className="rating-distribution">
                                {[5,4,3,2,1].map(stars => (
                                    <div key={stars} className="d-flex align-items-center mb-1">
                                        <span className="me-2">{stars}★</span>
                                        <div className="progress flex-grow-1" style={{height: '10px'}}>
                                            <div 
                                                className="progress-bar bg-warning" 
                                                style={{
                                                    width: `${(ratingStats.ratingDistribution[stars] / ratingStats.totalRatings * 100) || 0}%`
                                                }}
                                            ></div>
                                        </div>
                                        <span className="ms-2">{ratingStats.ratingDistribution[stars]}</span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Recent Rentals Table */}
            {stats.recentRentals && stats.recentRentals.length > 0 && (
                <div className="mt-4">
                    <h3>Recent Rentals</h3>
                    <div className="table-responsive">
                        <table className="table">
                            <thead>
                                <tr>
                                    <th>Tool</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Rating</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {stats.recentRentals.map(rental => (
                                    <tr key={rental.id}>
                                        <td>{rental.tool?.name}</td>
                                        <td>{new Date(rental.start_date).toLocaleDateString()}</td>
                                        <td>{new Date(rental.end_date).toLocaleDateString()}</td>
                                        <td>
                                            <span className={`badge ${
                                                rental.status === 'active' ? 'bg-success' : 
                                                rental.status === 'completed' ? 'bg-secondary' : 
                                                'bg-warning'
                                            }`}>
                                                {rental.status}
                                            </span>
                                        </td>
                                        <td>${rental.total_price}</td>
                                        <td>
                                            {rental.rating ? (
                                                <ReactStars
                                                    count={5}
                                                    value={rental.rating}
                                                    edit={false}
                                                    size={20}
                                                    activeColor="#ffd700"
                                                />
                                            ) : '-'}
                                        </td>
                                        <td>
                                            {rental.status === 'active' && (
                                                <button 
                                                    className="btn btn-warning btn-sm"
                                                    onClick={() => handleReturnTool(rental.id)}
                                                >
                                                    Return Tool
                                                </button>
                                            )}
                                            {rental.status === 'completed' && !rental.rating && (
                                                <button 
                                                    className="btn btn-primary btn-sm"
                                                    onClick={() => {
                                                        console.log('Opening rating modal for rental:', rental);
                                                        setSelectedRental(rental);
                                                        setRating(5); // Reset rating
                                                        setReview(''); // Reset review
                                                        setShowRatingModal(true);
                                                    }}
                                                >
                                                    Rate & Review
                                                </button>
                                            )}
                                            <Link 
                                                to={`/tools/${rental.tool.id}/rent`}
                                                className="btn btn-success btn-sm ms-2"
                                            >
                                                Rent Again
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {/* Rating Modal */}
            {showRatingModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40">
                    <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                        <h3 className="text-lg font-semibold mb-4">Rate Your Rental</h3>
                        <div className="mb-3">
                            <label className="form-label">Rating</label>
                            <div className="d-flex justify-content-center">
                                <ReactStars
                                    count={5}
                                    value={rating}
                                    onChange={setRating}
                                    size={40}
                                    activeColor="#ffd700"
                                />
                            </div>
                        </div>
                        <div className="mb-3">
                            <label className="form-label">Review</label>
                            <textarea 
                                className="form-control"
                                value={review}
                                onChange={(e) => setReview(e.target.value)}
                                rows="3"
                            ></textarea>
                        </div>
                        <div className="flex justify-end space-x-3 mt-6">
                            <button
                                onClick={() => {
                                    setShowRatingModal(false);
                                    setSelectedRental(null);
                                }}
                                className="px-4 py-2 text-gray-600 hover:text-gray-800 rounded"
                                disabled={loading}
                            >
                                Cancel
                            </button>
                            <button
                                onClick={() => handleRateRental(selectedRental.id)}
                                className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
                                disabled={loading}
                            >
                                {loading ? (
                                    <div className="flex items-center">
                                        <LoadingSpinner size="small" />
                                        <span className="ml-2">Submitting...</span>
                                    </div>
                                ) : (
                                    'Submit Rating'
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Dashboard; 