import React, { useState } from 'react';
import axios from 'axios';

const ToolForm = () => {
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        daily_rate: '',
        category_id: '',
        image: null
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        const data = new FormData();
        
        for (const key in formData) {
            data.append(key, formData[key]);
        }

        try {
            await axios.post('/api/tools', data, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            // Handle success (e.g., show message, redirect)
        } catch (error) {
            console.error('Error creating tool:', error);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="max-w-md mx-auto">
            <div className="mb-4">
                <label className="block mb-2">Tool Name</label>
                <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({...formData, name: e.target.value})}
                    className="w-full border rounded px-3 py-2"
                    required
                />
            </div>
            {/* Add other form fields */}
            <button type="submit" className="bg-green-500 text-white px-4 py-2 rounded">
                Add Tool
            </button>
        </form>
    );
};

export default ToolForm; 