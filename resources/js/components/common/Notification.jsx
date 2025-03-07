import { useState, useEffect } from 'react';

const Notification = ({ message, type = 'success', duration = 3000, onClose }) => {
    const [isVisible, setIsVisible] = useState(true);

    useEffect(() => {
        const timer = setTimeout(() => {
            setIsVisible(false);
            onClose?.();
        }, duration);

        return () => clearTimeout(timer);
    }, [duration, onClose]);

    const bgColor = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    }[type];

    if (!isVisible) return null;

    return (
        <div className={`fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center`}>
            <span>{message}</span>
            <button 
                onClick={() => {
                    setIsVisible(false);
                    onClose?.();
                }}
                className="ml-4 hover:text-gray-200"
            >
                ×
            </button>
        </div>
    );
};

export default Notification; 