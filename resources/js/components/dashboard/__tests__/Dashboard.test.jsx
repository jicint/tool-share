import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { act } from 'react-dom/test-utils';
import axios from 'axios';
import Dashboard from '../Dashboard';
import { AuthProvider } from '../../../contexts/AuthContext';

// Mock axios
jest.mock('axios');

// Mock the auth context
jest.mock('../../../contexts/AuthContext', () => ({
    useAuth: () => ({
        token: 'fake-token',
        user: { id: 1 }
    })
}));

describe('Dashboard Component', () => {
    const mockDashboardData = {
        totalRentals: 5,
        activeRentals: 2,
        recentRentals: [
            {
                id: 1,
                tool: { id: 1, name: 'Test Tool' },
                start_date: '2025-02-25',
                end_date: '2025-02-26',
                status: 'active',
                total_price: '25.00'
            }
        ],
        totalSpent: 100
    };

    const mockRatingStats = {
        averageRating: 4.5,
        totalRatings: 10,
        ratingDistribution: {
            5: 5,
            4: 3,
            3: 2,
            2: 0,
            1: 0
        }
    };

    beforeEach(() => {
        axios.get.mockImplementation((url) => {
            if (url === '/api/dashboard') {
                return Promise.resolve({ data: mockDashboardData });
            }
            if (url === '/api/rentals/rating-stats') {
                return Promise.resolve({ data: mockRatingStats });
            }
        });
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    test('renders dashboard with loading state', async () => {
        render(<Dashboard />);
        expect(screen.getByText(/loading/i)).toBeInTheDocument();
    });

    test('renders dashboard data after loading', async () => {
        render(<Dashboard />);
        
        await waitFor(() => {
            expect(screen.getByText('Total Rentals')).toBeInTheDocument();
            expect(screen.getByText('5')).toBeInTheDocument();
        });
    });

    test('shows confirmation dialog when returning tool', async () => {
        render(<Dashboard />);

        await waitFor(() => {
            expect(screen.getByText('Return Tool')).toBeInTheDocument();
        });

        fireEvent.click(screen.getByText('Return Tool'));
        
        expect(screen.getByText('Confirm Return')).toBeInTheDocument();
        expect(screen.getByText('Are you sure you want to return this tool?')).toBeInTheDocument();
    });

    test('handles rating submission', async () => {
        axios.post.mockResolvedValueOnce({ data: { message: 'Rating submitted successfully' } });

        render(<Dashboard />);

        await waitFor(() => {
            expect(screen.getByText('Rate & Review')).toBeInTheDocument();
        });

        fireEvent.click(screen.getByText('Rate & Review'));
        
        // Fill in rating form
        await userEvent.type(screen.getByLabelText(/review/i), 'Great tool!');
        
        fireEvent.click(screen.getByText('Submit Rating'));

        await waitFor(() => {
            expect(axios.post).toHaveBeenCalledWith(
                expect.stringContaining('/rate'),
                expect.objectContaining({
                    rating: 5,
                    review: 'Great tool!'
                }),
                expect.any(Object)
            );
        });
    });

    test('shows error notification on API failure', async () => {
        axios.post.mockRejectedValueOnce(new Error('API Error'));

        render(<Dashboard />);

        await waitFor(() => {
            expect(screen.getByText('Rate & Review')).toBeInTheDocument();
        });

        fireEvent.click(screen.getByText('Rate & Review'));
        fireEvent.click(screen.getByText('Submit Rating'));

        await waitFor(() => {
            expect(screen.getByText(/failed to submit rating/i)).toBeInTheDocument();
        });
    });
}); 