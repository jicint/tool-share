import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const Dashboard = () => {
    const navigate = useNavigate();
    const { user, logout } = useAuth();

    const handleLogout = async () => {
        await logout();
        navigate('/login');
    };

    return (
        <div className="min-h-screen bg-gray-100">
            {/* Navigation */}
            <nav className="bg-white shadow">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="flex-shrink-0 flex items-center">
                                <h1 className="text-xl font-bold">Tool Share</h1>
                            </div>
                        </div>
                        <div className="flex items-center">
                            <span className="text-gray-700 mr-4">Welcome, {user?.name}</span>
                            <button
                                onClick={handleLogout}
                                className="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                            >
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Main Content */}
            <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <div className="px-4 py-6 sm:px-0">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {/* Quick Actions */}
                        <div className="bg-white overflow-hidden shadow rounded-lg">
                            <div className="p-5">
                                <h3 className="text-lg font-medium text-gray-900">My Tools</h3>
                                <p className="mt-1 text-sm text-gray-500">Manage your shared tools</p>
                                <div className="mt-4 space-y-2">
                                    <button 
                                        onClick={() => navigate('/tools/add')}
                                        className="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                                    >
                                        Add New Tool
                                    </button>
                                    <button 
                                        onClick={() => navigate('/tools')}
                                        className="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                                    >
                                        View My Tools
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* Active Bookings */}
                        <div className="bg-white overflow-hidden shadow rounded-lg">
                            <div className="p-5">
                                <h3 className="text-lg font-medium text-gray-900">Active Bookings</h3>
                                <p className="mt-1 text-sm text-gray-500">View your current tool bookings</p>
                                <button className="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                                    View Bookings
                                </button>
                            </div>
                        </div>

                        {/* Profile */}
                        <div className="bg-white overflow-hidden shadow rounded-lg">
                            <div className="p-5">
                                <h3 className="text-lg font-medium text-gray-900">Profile</h3>
                                <p className="mt-1 text-sm text-gray-500">Update your information</p>
                                <button className="mt-4 bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                                    Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard; 