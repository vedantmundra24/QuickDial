
-- quickdial db

CREATE DATABASE IF NOT EXISTS quickdial_db;
USE quickdial_db;

-- business categories

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'fa-store',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sample categories

INSERT INTO categories (name, icon) VALUES
('Restaurants',      'fa-utensils'),
('Hospitals',        'fa-hospital'),
('Hotels',           'fa-hotel'),
('Shopping',         'fa-shopping-bag'),
('Education',        'fa-graduation-cap'),
('Salons',           'fa-cut'),
('Gyms',             'fa-dumbbell'),
('Plumbers',         'fa-wrench'),
('Electricians',     'fa-bolt'),
('Lawyers',          'fa-gavel'),
('Real Estate',      'fa-home'),
('Automobile',       'fa-car'),
('Travels',          'fa-plane'),
('Banks',            'fa-university'),
('Pharmacies',       'fa-pills');

-- users

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    city VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sample user (password: user123)

INSERT INTO users (name, email, password, phone, city) VALUES
('Rahul Sharma',  'rahul@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'Mumbai'),
('Priya Mehta',   'priya@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9123456780', 'Delhi'),
('Amit Kumar',    'amit@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9988776655', 'Bangalore');

-- admin

CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- default admin (password: admin123)

INSERT INTO admin (username, password, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@quickdial.com');

-- businesses

CREATE TABLE IF NOT EXISTS businesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    pincode VARCHAR(10),
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(150),
    website VARCHAR(200),
    opening_time TIME,
    closing_time TIME,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sample businesses approved one 

INSERT INTO businesses (user_id, category_id, name, description, address, city, state, pincode, phone, email, website, opening_time, closing_time, status, featured) VALUES
(1, 1,  'Spice Garden Restaurant',    'Authentic Indian cuisine with a modern twist. Best biryani in town.',         '45 Marine Drive, Colaba',         'Mumbai',    'Maharashtra', '400001', '9812345670', 'spicegarden@email.com',    'www.spicegarden.com',    '08:00:00', '23:00:00', 'approved', 1),
(1, 2,  'City Care Hospital',         'Multi-specialty hospital with 24/7 emergency services.',                     '12 Link Road, Andheri West',      'Mumbai',    'Maharashtra', '400058', '9823456781', 'citycare@email.com',       'www.citycarehospital.com','00:00:00', '23:59:00', 'approved', 1),
(2, 3,  'Grand Plaza Hotel',          'Luxury 5-star hotel with rooftop pool and spa.',                             '78 Connaught Place',              'Delhi',     'Delhi',       '110001', '9834567892', 'grandplaza@email.com',     'www.grandplazahotel.com', '00:00:00', '23:59:00', 'approved', 1),
(2, 4,  'Fashion Hub Mall',           'Premier shopping destination with 200+ brands.',                             '23 Lajpat Nagar',                 'Delhi',     'Delhi',       '110024', '9845678903', 'fashionhub@email.com',     'www.fashionhubmall.com',  '10:00:00', '22:00:00', 'approved', 0),
(3, 5,  'Bright Future Academy',      'Top-rated coaching institute for IIT-JEE and NEET preparation.',             '56 Koramangala, 4th Block',       'Bangalore', 'Karnataka',   '560034', '9856789014', 'brightfuture@email.com',   'www.brightfuture.edu',    '07:00:00', '21:00:00', 'approved', 0),
(3, 6,  'Glamour Salon & Spa',        'Premium unisex salon offering haircut, coloring, facials and more.',         '89 Indiranagar, 100 Feet Road',   'Bangalore', 'Karnataka',   '560038', '9867890125', 'glamour@email.com',        'www.glamoursalon.com',    '09:00:00', '20:00:00', 'approved', 1),
(1, 7,  'Fit Zone Gym',               'State-of-the-art gym with certified trainers and nutrition guidance.',        '34 Juhu Scheme',                  'Mumbai',    'Maharashtra', '400049', '9878901236', 'fitzone@email.com',        'www.fitzonegym.com',      '05:00:00', '23:00:00', 'approved', 0),
(2, 11, 'Dream Homes Realty',         'Trusted real estate company for buying, selling and renting properties.',    '67 DLF Phase 2, Gurgaon',         'Delhi',     'Haryana',     '122002', '9889012347', 'dreamhomes@email.com',     'www.dreamhomesrealty.com','09:00:00', '19:00:00', 'approved', 0),
(3, 1,  'The South Indian Kitchen',   'Traditional South Indian meals, dosas, idlis, and filter coffee.',           '12 Jayanagar 4th Block',          'Bangalore', 'Karnataka',   '560041', '9890123458', 'southkitchen@email.com',   '',                        '07:00:00', '22:00:00', 'approved', 0),
(1, 9,  'Quick Fix Electricals',      '24/7 electrical repair and installation services at your doorstep.',         '3 Dharavi Cross Road',            'Mumbai',    'Maharashtra', '400017', '9901234569', 'quickfix@email.com',       '',                        '08:00:00', '20:00:00', 'approved', 0),
(2, 2,  'Apollo Life Clinic',         'Specialized clinic for cardiology, orthopedics, and general medicine.',      '45 Vasant Vihar',                 'Delhi',     'Delhi',       '110057', '9812340001', 'apollolife@email.com',     'www.apollolife.com',      '09:00:00', '21:00:00', 'approved', 1),
(3, 13, 'Horizon Travels & Tours',    'Affordable holiday packages, visa assistance, and flight bookings.',         '9 MG Road, Brigade Road',         'Bangalore', 'Karnataka',   '560001', '9823451002', 'horizon@email.com',        'www.horizontravels.com',  '09:00:00', '18:00:00', 'approved', 0);

-- reviews

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    reviewer_name VARCHAR(100) NOT NULL,
    reviewer_email VARCHAR(150),
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sample reviews

INSERT INTO reviews (business_id, user_id, reviewer_name, reviewer_email, rating, comment) VALUES
(1, 2, 'Priya Mehta',   'priya@example.com',  5, 'Absolutely loved the biryani! Ambiance is great and staff is very friendly.'),
(1, 3, 'Amit Kumar',    'amit@example.com',   4, 'Good food with reasonable prices. Will definitely visit again.'),
(2, 2, 'Priya Mehta',   'priya@example.com',  5, 'Best hospital experience ever. Doctors are very professional and caring.'),
(3, 1, 'Rahul Sharma',  'rahul@example.com',  4, 'Luxurious hotel with amazing rooftop pool. Service was top notch.'),
(4, 3, 'Amit Kumar',    'amit@example.com',   3, 'Good variety of shops. Parking can be an issue on weekends.'),
(5, 1, 'Rahul Sharma',  'rahul@example.com',  5, 'My son cracked IIT because of this institute. Highly recommended!'),
(6, 2, 'Priya Mehta',   'priya@example.com',  5, 'Best salon in Bangalore. The hair treatment was phenomenal!'),
(7, 3, 'Amit Kumar',    'amit@example.com',   4, 'Modern equipment and experienced trainers. Love working out here.'),
(9, 1, 'Rahul Sharma',  'rahul@example.com',  4, 'Authentic south Indian food. The filter coffee is amazing!'),
(11, 3, 'Amit Kumar',   'amit@example.com',   5, 'Very professional doctors. Got my heart check done without any wait.'),
(12, 2, 'Priya Mehta',  'priya@example.com',  4, 'Booked a Goa trip through them. Everything was perfectly arranged.');

-- contact messages

CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(15),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('unread','read','replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- business ratings

CREATE OR REPLACE VIEW business_ratings AS
SELECT
    b.id,
    b.name,
    b.city,
    b.phone,
    b.address,
    b.status,
    b.featured,
    c.name AS category_name,
    c.icon AS category_icon,
    COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
    COUNT(r.id) AS review_count
FROM businesses b
LEFT JOIN categories c ON b.category_id = c.id
LEFT JOIN reviews r ON b.id = r.business_id
GROUP BY b.id, b.name, b.city, b.phone, b.address, b.status, b.featured, c.name, c.icon;
