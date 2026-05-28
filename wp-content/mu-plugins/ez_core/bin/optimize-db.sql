-- EZ Core Performance Indices
-- Apply these to achieve P95 < 250ms for team/panel actions.

-- External Database (escapezo_queries)
ALTER TABLE wp_zb_booking_history ADD INDEX IF NOT EXISTS idx_room_time_status (room_id, booking_time, status, booked_time);

-- WordPress Database
ALTER TABLE booking_lock_schedule ADD INDEX IF NOT EXISTS idx_prod_time_lock (product_id, booking_time, lock_time);
