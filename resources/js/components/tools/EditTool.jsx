import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';

// Add this function at the top level
const reorder = (list, startIndex, endIndex) => {
    const result = Array.from(list);
    const [removed] = result.splice(startIndex, 1);
    result.splice(endIndex, 0, removed);
    return result;
};

const EditTool = () => {
    const navigate = useNavigate();
    const { id } = useParams();
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [images, setImages] = useState([]);
    const [imagePreviews, setImagePreviews] = useState([]);
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        category: '',
        daily_rate: '',
        condition: '',
        availability_status: true,
        image_path: ''
    });

    const categories = [
        'Power Tools',
        'Hand Tools',
        'Garden Tools',
        'Cleaning Equipment',
        'Measuring Tools',
        'Safety Equipment',
        'Other'
    ];

    useEffect(() => {
        fetchTool();
    }, [id]);

    const fetchTool = async () => {
        try {
            const response = await axios.get(`/api/tools/${id}`);
            setFormData(response.data);
            
            // Handle multiple images
            if (response.data.images && response.data.images.length > 0) {
                const baseUrl = window.location.origin;
                const imageUrls = response.data.images.map(img => 
                    `${baseUrl}/storage/${img.image_path}`
                );
                setImagePreviews(imageUrls);
            }
            setLoading(false);
        } catch (error) {
            console.error('Error fetching tool:', error);
            setError('Failed to fetch tool details');
            setLoading(false);
        }
    };

    const handleImageChange = (e) => {
        const files = Array.from(e.target.files);
        
        // Maximum 5 images total
        if (imagePreviews.length + files.length > 5) {
            setError('Maximum 5 images allowed');
            return;
        }

        files.forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                setError('Each image must be less than 5MB');
                return;
            }

            const img = new Image();
            img.src = URL.createObjectURL(file);
            
            img.onload = () => {
                if (img.width < 200 || img.height < 200) {
                    setError('Images must be at least 200x200 pixels');
                    URL.revokeObjectURL(img.src);
                    return;
                }
                if (img.width > 1000 || img.height > 1000) {
                    setError('Images must be no larger than 1000x1000 pixels');
                    URL.revokeObjectURL(img.src);
                    return;
                }

                setImages(prev => [...prev, file]);
                setImagePreviews(prev => [...prev, img.src]);
                setError('');
            };

            img.onerror = () => {
                setError('Invalid image file');
                URL.revokeObjectURL(img.src);
            };
        });
    };

    const removeImage = (index) => {
        setImages(prevImages => prevImages.filter((_, i) => i !== index));
        setImagePreviews(prevPreviews => prevPreviews.filter((_, i) => i !== index));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        
        try {
            const formPayload = new FormData();
            
            // Append all form data
            Object.keys(formData).forEach(key => {
                if (key === 'availability_status') {
                    formPayload.append(key, formData[key] ? '1' : '0');
                } else if (key !== 'image_path') { // Don't append the image_path
                    formPayload.append(key, formData[key]);
                }
            });
            
            // Append new images if selected
            images.forEach((image, index) => {
                formPayload.append('images[]', image);
            });

            // Use POST method with _method parameter for Laravel to handle it as PUT
            await axios.post(`/api/tools/${id}`, formPayload, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-HTTP-Method-Override': 'PUT' // Laravel will treat this as PUT request
                }
            });
            
            navigate('/tools');
        } catch (error) {
            setError(error.response?.data?.message || 'Failed to update tool');
        } finally {
            setLoading(false);
        }
    };

    const onDragEnd = (result) => {
        // dropped outside the list
        if (!result.destination) {
            return;
        }

        console.log('Dragging from', result.source.index, 'to', result.destination.index);

        const reorderedPreviews = reorder(
            imagePreviews,
            result.source.index,
            result.destination.index
        );

        const reorderedImages = reorder(
            images,
            result.source.index,
            result.destination.index
        );

        setImagePreviews(reorderedPreviews);
        setImages(reorderedImages);
    };

    if (loading) {
        return (
            <div className="flex justify-center items-center min-h-screen">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="bg-white shadow">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="flex-shrink-0 flex items-center">
                                <h1 className="text-xl font-bold">Edit Tool</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
                <div className="md:grid md:grid-cols-3 md:gap-6">
                    <div className="md:col-span-1">
                        <div className="px-4 sm:px-0">
                            <h3 className="text-lg font-medium leading-6 text-gray-900">Edit Tool</h3>
                            <p className="mt-1 text-sm text-gray-600">
                                Update your tool's information and images.
                            </p>
                            <div className="mt-4">
                                <button
                                    onClick={() => navigate('/tools')}
                                    className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    ← Back to Tools
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="mt-5 md:mt-0 md:col-span-2">
                        <div className="shadow sm:rounded-md sm:overflow-hidden">
                            <div className="px-4 py-5 bg-white space-y-6 sm:p-6">
                                {error && (
                                    <div className="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                                        <div className="text-red-700">{error}</div>
                                    </div>
                                )}

                                <form onSubmit={handleSubmit} className="space-y-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Tool Name</label>
                                        <input
                                            type="text"
                                            required
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value={formData.name}
                                            onChange={(e) => setFormData({...formData, name: e.target.value})}
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea
                                            required
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            rows="4"
                                            value={formData.description}
                                            onChange={(e) => setFormData({...formData, description: e.target.value})}
                                        />
                                    </div>

                                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Category</label>
                                            <select
                                                required
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                value={formData.category}
                                                onChange={(e) => setFormData({...formData, category: e.target.value})}
                                            >
                                                <option value="">Select a category</option>
                                                {categories.map((category) => (
                                                    <option key={category} value={category}>{category}</option>
                                                ))}
                                            </select>
                                        </div>

                                        <div>
                                            <label className="block text-sm font-medium text-gray-700">Daily Rate (€)</label>
                                            <div className="mt-1 relative rounded-md shadow-sm">
                                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span className="text-gray-500 sm:text-sm">€</span>
                                                </div>
                                                <input
                                                    type="number"
                                                    required
                                                    min="0"
                                                    step="0.01"
                                                    className="mt-1 block w-full pl-7 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    value={formData.daily_rate}
                                                    onChange={(e) => setFormData({...formData, daily_rate: e.target.value})}
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Condition</label>
                                        <select
                                            required
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value={formData.condition}
                                            onChange={(e) => setFormData({...formData, condition: e.target.value})}
                                        >
                                            <option value="excellent">Excellent - Like New</option>
                                            <option value="good">Good - Works Well</option>
                                            <option value="fair">Fair - Functional</option>
                                        </select>
                                    </div>

                                    <div className="flex items-center">
                                        <input
                                            type="checkbox"
                                            className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                            checked={formData.availability_status}
                                            onChange={(e) => setFormData({...formData, availability_status: e.target.checked})}
                                        />
                                        <label className="ml-2 block text-sm text-gray-700">
                                            Available for rent
                                        </label>
                                    </div>

                                    {/* Image Upload Section */}
                                    <div className="mt-1 space-y-4">
                                        <DragDropContext onDragEnd={onDragEnd}>
                                            <Droppable droppableId="droppable" direction="horizontal">
                                                {(provided, snapshot) => (
                                                    <div
                                                        {...provided.droppableProps}
                                                        ref={provided.innerRef}
                                                        className="flex flex-wrap gap-4"
                                                        style={{ minHeight: '100px' }}
                                                    >
                                                        {imagePreviews.map((preview, index) => (
                                                            <Draggable 
                                                                key={`image-${index}`} 
                                                                draggableId={`image-${index}`} 
                                                                index={index}
                                                            >
                                                                {(provided, snapshot) => (
                                                                    <div
                                                                        ref={provided.innerRef}
                                                                        {...provided.draggableProps}
                                                                        {...provided.dragHandleProps}
                                                                        className={`relative group ${
                                                                            snapshot.isDragging ? 'z-50 shadow-xl' : ''
                                                                        }`}
                                                                    >
                                                                        <div className="flex justify-center items-center w-20 h-20 border-2 border-gray-300 rounded-lg overflow-hidden bg-gray-50 relative">
                                                                            <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200">
                                                                                <svg 
                                                                                    className="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" 
                                                                                    fill="none" 
                                                                                    stroke="currentColor" 
                                                                                    viewBox="0 0 24 24"
                                                                                >
                                                                                    <path 
                                                                                        strokeLinecap="round" 
                                                                                        strokeLinejoin="round" 
                                                                                        strokeWidth="2" 
                                                                                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                                                                                    />
                                                                                </svg>
                                                                            </div>
                                                                            <img
                                                                                src={preview}
                                                                                alt={`Tool preview ${index + 1}`}
                                                                                className="w-full h-full object-cover"
                                                                            />
                                                                        </div>
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => removeImage(index)}
                                                                            className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                                                        >
                                                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                                                            </svg>
                                                                        </button>
                                                                        <div className="absolute -bottom-1 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded-full">
                                                                            {index + 1}
                                                                        </div>
                                                                    </div>
                                                                )}
                                                            </Draggable>
                                                        ))}
                                                        {provided.placeholder}
                                                        {imagePreviews.length < 5 && (
                                                            <div className="flex justify-center items-center w-20 h-20 border-2 border-gray-300 border-dashed rounded-lg bg-gray-50">
                                                                <input
                                                                    type="file"
                                                                    accept="image/*"
                                                                    onChange={handleImageChange}
                                                                    multiple
                                                                    className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                                />
                                                                <div className="text-gray-400 text-center">
                                                                    <svg className="mx-auto h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                                                                    </svg>
                                                                    <p className="text-xs">Add image</p>
                                                                </div>
                                                            </div>
                                                        )}
                                                    </div>
                                                )}
                                            </Droppable>
                                        </DragDropContext>
                                        <p className="text-sm text-gray-500">
                                            Drag to reorder • Upload up to 5 images (200x200 to 1000x1000 pixels, max 5MB each)
                                        </p>
                                        {error && (
                                            <p className="text-sm text-red-600">{error}</p>
                                        )}
                                    </div>

                                    <div className="flex justify-end space-x-3">
                                        <button
                                            type="button"
                                            onClick={() => navigate('/tools')}
                                            className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                                        >
                                            Update Tool
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default EditTool; 