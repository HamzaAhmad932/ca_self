let state = {
    analytics: {
        all_properties: 0,
        active_properties: 0,
        all_booking_sources: 0,
        line: {
            labels: [],
            values: []
        },
        pie: {
            labels: [],
            values: []
        },
        total_sale: [],
        client: {},
        show_line_graph: false
    },
    upcoming_arrivals: [],

    notifications: {
        notifications: [],
        total_available_notifications: 0,
        total_unread_notifications: 0,
        current_showing_notification_count: 0
    },
    total_notifications: 0,
    notifications_counts: 0,
};

export default state;
