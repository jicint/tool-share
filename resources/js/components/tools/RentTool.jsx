import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useAuth } from '../../contexts/AuthContext';

const RentTool = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { token } = useAuth();
    const [tool, setTool] = useState(null);
    const [formData, setFormData] = useState({
        start_date: '',
        end_date: '',
        total_price: 0
    });
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!token) {
            navigate('/login');
            return;
        }
        loadTool();
    }, [token]);

    const loadTool = async () => {
        try {
            const response = await axios.get(`/api/tools/${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            setTool(response.data);
            setLoading(false);
        } catch (error) {
            setError('Failed to load tool');
            setLoading(false);
        }
    };

    const calculateTotalPrice = (start, end, dailyRate) => {
        if (!start || !end || !dailyRate) return 0;
        const startDate = new Date(start);
        const endDate = new Date(end);
        const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        return (days * dailyRate).toFixed(2);
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));

        // Calculate total price when dates change
        if ((name === 'start_date' || name === 'end_date') && tool) {
            const newStartDate = name === 'start_date' ? value : formData.start_date;
            const newEndDate = name === 'end_date' ? value : formData.end_date;
            
            if (newStartDate && newEndDate) {
                const total = calculateTotalPrice(newStartDate, newEndDate, tool.daily_rate);
                setFormData(prev => ({ 
                    ...prev, 
                    [name]: value,
                    total_price: parseFloat(total)
                }));
            }
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!formData.total_price) {
            setError('Please select valid dates to calculate total price');
            return;
        }

        try {
            const payload = {
                start_date: formData.start_date,
                end_date: formData.end_date,
                total_price: parseFloat(formData.total_price)
            };

            console.log('Sending payload:', payload); // Debug log

            await axios.post(`/api/tools/${id}/rent`, payload, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            navigate('/tools');
        } catch (error) {
            console.error('Rental error:', error.response?.data); // Debug log
            setError(error.response?.data?.message || 'Failed to rent tool');
        }
    };

    if (loading) return <div>Loading...</div>;
    if (error) return <div className="alert alert-danger">{error}</div>;
    if (!tool) return <div>Tool not found</div>;

    return (
        <div className="container mt-5">
            <h2>Rent Tool: {tool.name}</h2>
            <div className="card mb-4">
                <div className="card-body">
                    <h5 className="card-title">{tool.name}</h5>
                    <p className="card-text">{tool.description}</p>
                    <p><strong>Daily Rate:</strong> ${tool.daily_rate}</p>
                </div>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label className="form-label">Start Date</label>
                    <input
                        type="date"
                        className="form-control"
                        name="start_date"
                        value={formData.start_date}
                        onChange={handleChange}
                        min={new Date().toISOString().split('T')[0]}
                        required
                    />
                </div>
                <div className="mb-3">
                    <label className="form-label">End Date</label>
                    <input
                        type="date"
                        className="form-control"
                        name="end_date"
                        value={formData.end_date}
                        onChange={handleChange}
                        min={formData.start_date || new Date().toISOString().split('T')[0]}
                        required
                    />
                </div>
                <div className="mb-3">
                    <label className="form-label">Total Price</label>
                    <div className="form-control">
                        ${formData.total_price || '0.00'}
                    </div>
                </div>
                <button 
                    type="submit" 
                    className="btn btn-primary"
                    disabled={!formData.total_price}
                >
                    Rent Tool
                </button>
            </form>
        </div>
    );
};

export default RentTool; 