-- Create the database
CREATE DATABASE IF NOT EXISTS mod5_admin;
USE mod5_admin;

-- Create the admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,          -- Used for login (auth.php)
    password VARCHAR(255) NOT NULL,                -- Hashed password (updated in profile.php)
    name VARCHAR(100) NOT NULL,                    -- Full name (displayed in profile.php and activity_log.php)
    email VARCHAR(100) NOT NULL UNIQUE,            -- Email address (editable in profile.php)
    avatar_url VARCHAR(255) DEFAULT NULL,          -- Path/URL to avatar image (updated in profile.php)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Account creation timestamp
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Last update timestamp
);

-- Create the activity_log table
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,                  -- e.g., "Logged in", "Cancelled Reservation ID X", "Confirmed Reservation ID X", "Edited Reservation ID X", "Deleted Reservation ID X"
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- When the action occurred
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Create the resorts table
CREATE TABLE resorts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,                   -- Resort name (displayed in calendar.php, admin_dashboard.php, confirmed_events.php, fetch_events.php)
    location VARCHAR(100) NOT NULL,               -- Resort location (e.g., "Honolulu, Hawaii")
    capacity INT NOT NULL,                        -- Maximum capacity of the resort
    amenities VARCHAR(255) NOT NULL               -- List of amenities (e.g., "Pool, Spa, Beach Access")
);

-- Create the reservations table with added email, amenities, and notes columns
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resort_id INT NOT NULL,                       -- Foreign key to resorts (calendar.php, admin_dashboard.php, fetch_events.php)
    full_name VARCHAR(100) NOT NULL,              -- Customer's full name (edit_reservation.php, fetch_events.php)
    email VARCHAR(100) DEFAULT NULL,              -- Customer's email (view_event.php)
    phone VARCHAR(20) NOT NULL,                   -- Customer's phone number (edit_reservation.php)
    event_type VARCHAR(50) NOT NULL,              -- e.g., "Wedding", "Corporate Event", etc. (edit_reservation.php, calendar.php, admin_dashboard.php)
    num_guests INT NOT NULL,                      -- Number of guests (edit_reservation.php, admin_dashboard.php, fetch_events.php)
    start_date DATE NOT NULL,                     -- Event start date (edit_reservation.php, calendar.php, admin_dashboard.php, fetch_events.php)
    time_in TIME NOT NULL,                        -- Event start time (edit_reservation.php, admin_dashboard.php)
    end_date DATE DEFAULT NULL,                   -- Event end date (edit_reservation.php, calendar.php, fetch_events.php)
    time_out TIME DEFAULT NULL,                   -- Event end time (edit_reservation.php, fetch_events.php)
    amenities VARCHAR(255) DEFAULT NULL,          -- Additional amenities requested (view_event.php)
    notes TEXT DEFAULT NULL,                      -- Additional notes (view_event.php)
    status ENUM('Pending', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending', -- Reservation status (updated in confirmation/cancellation scripts, admin_dashboard.php, fetch_events.php)
    payment_status ENUM('Pending', 'Paid') NOT NULL DEFAULT 'Pending',          -- Payment status (edit_reservation.php, admin_dashboard.php, fetch_events.php)
    payment_method ENUM('Cash', 'GCash', 'Credit Card') DEFAULT NULL,           -- Payment method (edit_reservation.php, admin_dashboard.php, fetch_events.php)
    amount DECIMAL(10, 2) DEFAULT 0.00,           -- Total amount for revenue calculation (admin_dashboard.php)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Reservation creation timestamp
    FOREIGN KEY (resort_id) REFERENCES resorts(id) ON DELETE CASCADE
);

-- Insert sample data into resorts
INSERT INTO resorts (id, name, location, capacity, amenities) VALUES
(1, 'Halekulani Hotel', 'Honolulu, Hawaii', 200, 'Pool, Spa, Beach Access'),
(2, 'Koloa Landing Resort at Poipu', 'Koa, Hawaii', 250, 'Pool, Golf Course, Spa'),
(3, 'Grand Hyatt Kauai Resort', 'Kauai, Hawaii', 300, 'Luxury Suites, Golf Course, Pool'),
(4, 'Hotel Hanalei Bay', 'Hanalei, Hawaii', 150, 'Ocean View, Pool, Private Beach'),
(5, 'Hyatt Regency Maui Resort', 'Maui, Hawaii', 280, 'Luau, Spa, Pool, Water Slides'),
(6, 'Sheraton Waikiki Beach Resort', 'Waikiki, Hawaii', 350, 'Beachfront, Infinity Pool, Shopping');

-- Insert reservations
-- INSERT INTO reservations (resort_id, full_name, email, phone, event_type, num_guests, start_date, time_in, end_date, time_out, amenities, notes, status, payment_status, payment_method, amount, created_at) 
-- VALUES 
-- (1, 'Emi Thasorn Klinnium', 'emi.t@example.com', '66912345681', 'Wedding', 40, '2025-04-10', '10:00:00', '2025-04-10', '18:00:00', 'Extra Chairs, Sound System', 'Fan wedding event', 'Pending', 'Paid', 'Cash', 4000.00, '2025-04-04 19:16:20'),
-- (2, 'Bonnie Pornsappitcha Pattarasopon', 'bonnie.p@example.com', '66912345682', 'Birthday', 25, '2025-04-11', '14:00:00', '2025-04-12', '20:00:00', 'Cake Table, Photo Booth', 'Fan birthday celebration', 'Pending', 'Paid', NULL, 2500.00, '2025-04-04 19:16:20');


-- -- Insert 50 reservations with Filipino, Korean, and Thai actors/actresses
-- INSERT INTO reservations (resort_id, full_name, email, phone, event_type, num_guests, start_date, time_in, end_date, time_out, amenities, notes, status, payment_status, payment_method, amount, created_at) 
-- VALUES 
-- -- Filipino Actors/Actresses (10)
-- (1, 'Kathryn Bernardo', 'kathryn.b@example.com', '09123456781', 'Wedding', 50, '2025-04-10', '10:00:00', '2025-04-10', '18:00:00', 'Extra Chairs', 'Intimate ceremony', 'Confirmed', 'Paid', 'Cash', 5000.00, '2025-04-04 19:16:20'),
-- (2, 'Daniel Padilla', 'daniel.p@example.com', '09123456782', 'Corporate Event', 30, '2025-04-11', '09:00:00', '2025-04-11', '17:00:00', 'Projector', 'Team meeting', 'Confirmed', 'Pending', 'GCash', 3000.00, '2025-04-04 19:16:20'),
-- (3, 'Liza Soberano', 'liza.s@example.com', '09123456783', 'Birthday', 25, '2025-04-12', '14:00:00', '2025-04-12', '20:00:00', 'Cake Table', '18th birthday', 'Pending', 'Pending', NULL, 2500.00, '2025-04-04 19:16:20'),
-- (4, 'James Reid', 'james.r@example.com', '09123456784', 'Reunion', 40, '2025-04-13', '11:00:00', '2025-04-13', '19:00:00', 'BBQ Grill', 'Family event', 'Cancelled', 'Paid', 'Credit Card', 4000.00, '2025-04-04 19:16:20'),
-- (5, 'Nadine Lustre', 'nadine.l@example.com', '09123456785', 'Conference', 60, '2025-04-14', '08:00:00', '2025-04-14', '16:00:00', 'Wi-Fi', 'Workshop', 'Confirmed', 'Paid', 'Credit Card', 6000.00, '2025-04-04 19:16:20'),
-- (6, 'Alden Richards', 'alden.r@example.com', '09123456786', 'Wedding', 45, '2025-04-15', '10:00:00', '2025-04-15', '18:00:00', 'Sound System', 'Church wedding', 'Confirmed', 'Paid', 'Cash', 4500.00, '2025-04-04 19:16:20'),
-- (1, 'Maine Mendoza', 'maine.m@example.com', '09123456787', 'Birthday', 30, '2025-04-16', '15:00:00', '2025-04-16', '21:00:00', 'Photo Booth', '21st birthday', 'Pending', 'Pending', NULL, 3000.00, '2025-04-04 19:16:20'),
-- (2, 'Enrique Gil', 'enrique.g@example.com', '09123456788', 'Corporate Event', 35, '2025-04-17', '09:00:00', '2025-04-17', '17:00:00', 'Catering', 'Seminar', 'Confirmed', 'Pending', 'GCash', 3500.00, '2025-04-04 19:16:20'),
-- (3, 'Bea Alonzo', 'bea.a@example.com', '09123456789', 'Reunion', 50, '2025-04-18', '12:00:00', '2025-04-18', '20:00:00', 'Karaoke', 'Class reunion', 'Cancelled', 'Paid', 'Credit Card', 5000.00, '2025-04-04 19:16:20'),
-- (4, 'John Lloyd Cruz', 'johnlloyd.c@example.com', '09123456790', 'Wedding', 55, '2025-04-19', '11:00:00', '2025-04-19', '19:00:00', 'Floral Decor', 'Garden wedding', 'Confirmed', 'Paid', 'Cash', 5500.00, '2025-04-04 19:16:20'),

-- -- Korean Actors/Actresses (20)
-- (5, 'Lee Min-ho', 'minho.l@example.com', '82123456781', 'Corporate Event', 40, '2025-04-20', '10:00:00', '2025-04-20', '18:00:00', 'Projector', 'Fan meeting', 'Confirmed', 'Paid', 'Credit Card', 4000.00, '2025-04-04 19:16:20'),
-- (6, 'Park Shin-hye', 'shinhye.p@example.com', '82123456782', 'Birthday', 20, '2025-04-21', '14:00:00', '2025-04-21', '20:00:00', 'Cake Table', 'Private party', 'Pending', 'Pending', NULL, 2000.00, '2025-04-04 19:16:20'),
-- (1, 'Kim Soo-hyun', 'soohyun.k@example.com', '82123456783', 'Wedding', 60, '2025-04-22', '11:00:00', '2025-04-22', '19:00:00', 'Sound System', 'Grand wedding', 'Confirmed', 'Paid', 'Cash', 6000.00, '2025-04-04 19:16:20'),
-- (2, 'Song Joong-ki', 'joongki.s@example.com', '82123456784', 'Reunion', 35, '2025-04-23', '12:00:00', '2025-04-23', '20:00:00', 'BBQ Grill', 'Drama cast reunion', 'Cancelled', 'Paid', 'Credit Card', 3500.00, '2025-04-04 19:16:20'),
-- (3, 'IU (Lee Ji-eun)', 'iu@example.com', '82123456785', 'Conference', 50, '2025-04-24', '09:00:00', '2025-04-24', '17:00:00', 'Wi-Fi', 'Music seminar', 'Confirmed', 'Pending', 'GCash', 5000.00, '2025-04-04 19:16:20'),
-- (4, 'Park Bo-gum', 'bogum.p@example.com', '82123456786', 'Birthday', 25, '2025-04-25', '15:00:00', '2025-04-25', '21:00:00', 'Photo Booth', 'Fan birthday', 'Pending', 'Pending', NULL, 2500.00, '2025-04-04 19:16:20'),
-- (5, 'Hyun Bin', 'hyunbin@example.com', '82123456787', 'Wedding', 45, '2025-04-26', '10:00:00', '2025-04-26', '18:00:00', 'Floral Decor', 'Beach wedding', 'Confirmed', 'Paid', 'Cash', 4500.00, '2025-04-04 19:16:20'),
-- (6, 'Son Ye-jin', 'yejin.s@example.com', '82123456788', 'Corporate Event', 30, '2025-04-27', '08:00:00', '2025-04-27', '16:00:00', 'Catering', 'Brand event', 'Confirmed', 'Paid', 'Credit Card', 3000.00, '2025-04-04 19:16:20'),
-- (1, 'Gong Yoo', 'gongyoo@example.com', '82123456789', 'Reunion', 40, '2025-04-28', '12:00:00', '2025-04-28', '20:00:00', 'Karaoke', 'Cast reunion', 'Cancelled', 'Paid', 'Credit Card', 4000.00, '2025-04-04 19:16:20'),
-- (2, 'Kim Tae-hee', 'taehee.k@example.com', '82123456790', 'Wedding', 55, '2025-04-29', '11:00:00', '2025-04-29', '19:00:00', 'Extra Chairs', 'Luxury wedding', 'Confirmed', 'Paid', 'Cash', 5500.00, '2025-04-04 19:16:20'),
-- (3, 'Ji Chang-wook', 'changwook.j@example.com', '82123456791', 'Birthday', 20, '2025-04-30', '14:00:00', '2025-04-30', '20:00:00', 'Cake Table', 'Fan event', 'Pending', 'Pending', NULL, 2000.00, '2025-04-04 19:16:20'),
-- (4, 'Park Seo-joon', 'seojoon.p@example.com', '82123456792', 'Corporate Event', 35, '2025-05-01', '09:00:00', '2025-05-01', '17:00:00', 'Projector', 'Promo event', 'Confirmed', 'Pending', 'GCash', 3500.00, '2025-04-04 19:16:20'),
-- (5, 'Han Hyo-joo', 'hyojoo.h@example.com', '82123456793', 'Reunion', 50, '2025-05-02', '12:00:00', '2025-05-02', '20:00:00', 'BBQ Grill', 'Friends reunion', 'Cancelled', 'Paid', 'Credit Card', 5000.00, '2025-04-04 19:16:20'),
-- (6, 'Song Hye-kyo', 'hyekyo.s@example.com', '82123456794', 'Wedding', 60, '2025-05-03', '10:00:00', '2025-05-03', '18:00:00', 'Sound System', 'Grand ceremony', 'Confirmed', 'Paid', 'Cash', 6000.00, '2025-04-04 19:16:20'),
-- (1, 'Bae Suzy', 'suzy.b@example.com', '82123456795', 'Conference', 40, '2025-05-04', '08:00:00', '2025-05-04', '16:00:00', 'Wi-Fi', 'Fan seminar', 'Confirmed', 'Paid', 'Credit Card', 4000.00, '2025-04-04 19:16:20'),
-- (2, 'Nam Joo-hyuk', 'joohyuk.n@example.com', '82123456796', 'Birthday', 25, '2025-05-05', '15:00:00', '2025-05-05', '21:00:00', 'Photo Booth', 'Private party', 'Pending', 'Pending', NULL, 2500.00, '2025-04-04 19:16:20'),
-- (3, 'Jung Hae-in', 'haein.j@example.com', '82123456797', 'Corporate Event', 30, '2025-05-06', '09:00:00', '2025-05-06', '17:00:00', 'Catering', 'Brand launch', 'Confirmed', 'Pending', 'GCash', 3000.00, '2025-04-04 19:16:20'),
-- (4, 'Kim Ji-won', 'jiwon.k@example.com', '82123456798', 'Reunion', 45, '2025-05-07', '12:00:00', '2025-05-07', '20:00:00', 'Karaoke', 'Cast reunion', 'Cancelled', 'Paid', 'Credit Card', 4500.00, '2025-04-04 19:16:20'),
-- (5, 'Lee Jong-suk', 'jongsuk.l@example.com', '82123456799', 'Wedding', 50, '2025-05-08', '11:00:00', '2025-05-08', '19:00:00', 'Floral Decor', 'Beach wedding', 'Confirmed', 'Paid', 'Cash', 5000.00, '2025-04-04 19:16:20'),
-- (6, 'Shin Min-a', 'mina.s@example.com', '82123456800', 'Conference', 35, '2025-05-09', '08:00:00', '2025-05-09', '16:00:00', 'Wi-Fi', 'Industry event', 'Confirmed', 'Paid', 'Credit Card', 3500.00, '2025-04-04 19:16:20'),

-- -- Thai GL Actresses (10)
-- (1, 'Freen Sarocha', 'freen.s@example.com', '66912345671', 'Wedding', 40, '2025-05-10', '10:00:00', '2025-05-10', '18:00:00', 'Extra Chairs', 'Fan wedding', 'Confirmed', 'Paid', 'Cash', 4000.00, '2025-04-04 19:16:20'),
-- (2, 'Becky Armstrong', 'becky.a@example.com', '66912345672', 'Birthday', 20, '2025-05-11', '14:00:00', '2025-05-11', '20:00:00', 'Cake Table', 'Fan birthday', 'Pending', 'Pending', NULL, 2000.00, '2025-04-04 19:16:20'),
-- (3, 'Engfa Waraha', 'engfa.w@example.com', '66912345673', 'Corporate Event', 30, '2025-05-12', '09:00:00', '2025-05-12', '17:00:00', 'Projector', 'Promo event', 'Confirmed', 'Pending', 'GCash', 3000.00, '2025-04-04 19:16:20'),
-- (4, 'Charlotte Austin', 'charlotte.a@example.com', '66912345674', 'Reunion', 35, '2025-05-13', '12:00:00', '2025-05-13', '20:00:00', 'BBQ Grill', 'Cast reunion', 'Cancelled', 'Paid', 'Credit Card', 3500.00, '2025-04-04 19:16:20'),
-- (5, 'Milk Pansa', 'milk.p@example.com', '66912345675', 'Wedding', 50, '2025-05-14', '11:00:00', '2025-05-14', '19:00:00', 'Sound System', 'Grand wedding', 'Confirmed', 'Paid', 'Cash', 5000.00, '2025-04-04 19:16:20'),
-- (6, 'Love Pattranite', 'love.p@example.com', '66912345676', 'Conference', 40, '2025-05-15', '08:00:00', '2025-05-15', '16:00:00', 'Wi-Fi', 'Fan seminar', 'Confirmed', 'Paid', 'Credit Card', 4000.00, '2025-04-04 19:16:20'),
-- (1, 'Namtan Tipnaree', 'namtan.t@example.com', '66912345677', 'Birthday', 25, '2025-05-16', '15:00:00', '2025-05-16', '21:00:00', 'Photo Booth', 'Private party', 'Pending', 'Pending', NULL, 2500.00, '2025-04-04 19:16:20'),
-- (2, 'Aye Sarunchana', 'aye.s@example.com', '66912345678', 'Corporate Event', 30, '2025-05-17', '09:00:00', '2025-05-17', '17:00:00', 'Catering', 'Brand event', 'Confirmed', 'Pending', 'GCash', 3000.00, '2025-04-04 19:16:20'),
-- (3, 'Lingling Kwong', 'lingling.k@example.com', '66912345679', 'Reunion', 45, '2025-05-18', '12:00:00', '2025-05-18', '20:00:00', 'Karaoke', 'Fan reunion', 'Cancelled', 'Paid', 'Credit Card', 4500.00, '2025-04-04 19:16:20'),
-- (4, 'Orm Kornnaphat', 'orm.k@example.com', '66912345680', 'Wedding', 55, '2025-05-19', '11:00:00', '2025-05-19', '19:00:00', 'Floral Decor', 'Beach wedding', 'Confirmed', 'Paid', 'Cash', 5500.00, '2025-04-04 19:16:20'),

-- -- Additional Korean Actors/Actresses (10)
-- (5, 'Cha Eun-woo', 'eunwoo.c@example.com', '82123456801', 'Birthday', 20, '2025-05-20', '14:00:00', '2025-05-20', '20:00:00', 'Cake Table', 'Fan birthday', 'Pending', 'Pending', NULL, 2000.00, '2025-04-04 19:16:20'),
-- (6, 'Kim Seon-ho', 'seonho.k@example.com', '82123456802', 'Corporate Event', 35, '2025-05-21', '09:00:00', '2025-05-21', '17:00:00', 'Projector', 'Promo event', 'Confirmed', 'Pending', 'GCash', 3500.00, '2025-04-04 19:16:20'),
-- (1, 'Park Min-young', 'minyoung.p@example.com', '82123456803', 'Reunion', 40, '2025-05-22', '12:00:00', '2025-05-22', '20:00:00', 'BBQ Grill', 'Cast reunion', 'Cancelled', 'Paid', 'Credit Card', 4000.00, '2025-04-04 19:16:20'),
-- (2, 'Ahn Hyo-seop', 'hyoseop.a@example.com', '82123456804', 'Wedding', 50, '2025-05-23', '10:00:00', '2025-05-23', '18:00:00', 'Sound System', 'Grand wedding', 'Confirmed', 'Paid', 'Cash', 5000.00, '2025-04-04 19:16:20'),
-- (3, 'Seo Ye-ji', 'yeji.s@example.com', '82123456805', 'Conference', 30, '2025-05-24', '08:00:00', '2025-05-24', '16:00:00', 'Wi-Fi', 'Seminar', 'Confirmed', 'Paid', 'Credit Card', 3000.00, '2025-04-04 19:16:20'),
-- (4, 'Lee Dong-wook', 'dongwook.l@example.com', '82123456806', 'Birthday', 25, '2025-05-25', '15:00:00', '2025-05-25', '21:00:00', 'Photo Booth', 'Fan event', 'Pending', 'Pending', NULL, 2500.00, '2025-04-04 19:16:20'),
-- (5, 'Yoo Yeon-seok', 'yeonseok.y@example.com', '82123456807', 'Corporate Event', 40, '2025-05-26', '09:00:00', '2025-05-26', '17:00:00', 'Catering', 'Brand launch', 'Confirmed', 'Pending', 'GCash', 4000.00, '2025-04-04 19:16:20'),
-- (6, 'Kim Go-eun', 'goeun.k@example.com', '82123456808', 'Reunion', 35, '2025-05-27', '12:00:00', '2025-05-27', '20:00:00', 'Karaoke', 'Friends reunion', 'Cancelled', 'Paid', 'Credit Card', 3500.00, '2025-04-04 19:16:20'),
-- (1, 'Kang Han-na', 'hanna.k@example.com', '82123456809', 'Wedding', 45, '2025-05-28', '11:00:00', '2025-05-28', '19:00:00', 'Floral Decor', 'Beach wedding', 'Confirmed', 'Paid', 'Cash', 4500.00, '2025-04-04 19:16:20'),
-- (2, 'Ok Taec-yeon', 'taecyeon.o@example.com', '82123456810', 'Conference', 50, '2025-05-29', '08:00:00', '2025-05-29', '16:00:00', 'Wi-Fi', 'Industry event', 'Confirmed', 'Paid', 'Credit Card', 5000.00, '2025-04-04 19:16:20');