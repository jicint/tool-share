import React, { useState } from 'react';
import axios from 'axios';

const CreateToolForm = () => {
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        category: '',
        daily_rate: '',
        condition: ''
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            const response = await axios.post('/api/tools', formData, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                }
            });
            window.location.href = '/tools'; // Redirect to tools index
        } catch (err) {
            console.error('Error:', err);
            setError(err.response?.data?.message || 'An error occurred while creating the tool.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            {error && (
                <div className="bg-red-50 border-l-4 border-red-400 p-4">
                    <p className="text-red-700">{error}</p>
                </div>
            )}

            <div>
                <label className="block text-sm font-medium text-gray-700">
                    Tool Name
                </label>
                <input
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    required
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea
                    name="description"
                    value={formData.description}
                    onChange={handleChange}
                    required
                    rows="3"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                ></textarea>
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700">
                    Category
                </label>
                <select
                    name="category"
                    value={formData.category}
                    onChange={handleChange}
                    required
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select a category</option>
                    <option value="power_tools">Power Tools</option>
                    <option value="hand_tools">Hand Tools</option>
                    <option value="garden_tools">Garden Tools</option>
                    <option value="measuring_tools">Measuring Tools</option>
                    <option value="cleaning_equipment">Cleaning Equipment</option>
                </select>
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700">
                    Daily Rate ($)
                </label>
                <input
                    type="number"
                    name="daily_rate"
                    value={formData.daily_rate}
                    onChange={handleChange}
                    required
                    min="0"
                    step="0.01"
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
            </div>

            <div>
                <label className="block text-sm font-medium text-gray-700">
                    Condition
                </label>
                <select
                    name="condition"
                    value={formData.condition}
                    onChange={handleChange}
                    required
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Select condition</option>
                    <option value="new">New</option>
                    <option value="excellent">Excellent</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                </select>
            </div>

            <div>
                <button
                    type="submit"
                    disabled={loading}
                    className={`w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 ${loading ? 'opacity-50 cursor-not-allowed' : ''}`}
                >
                    {loading ? 'Creating...' : 'Create Tool'}
                </button>
            </div>
        </form>
    );
};

export default CreateToolForm; 