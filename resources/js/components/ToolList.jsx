import React, { useState, useEffect } from 'react';
import axios from 'axios';

const ToolList = () => {
    const [tools, setTools] = useState([]);

    useEffect(() => {
        const fetchTools = async () => {
            try {
                const response = await axios.get('/api/tools');
                setTools(response.data);
            } catch (error) {
                console.error('Error fetching tools:', error);
            }
        };

        fetchTools();
    }, []);

    return (
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            {tools.map(tool => (
                <div key={tool.id} className="border p-4 rounded-lg shadow">
                    <img src={tool.image_url} alt={tool.name} className="w-full h-48 object-cover" />
                    <h3 className="text-xl font-bold mt-2">{tool.name}</h3>
                    <p className="text-gray-600">{tool.description}</p>
                    <p className="text-lg font-semibold mt-2">${tool.daily_rate}/day</p>
                    <button className="bg-blue-500 text-white px-4 py-2 rounded mt-2">
                        Book Now
                    </button>
                </div>
            ))}
        </div>
    );
};

export default ToolList; 