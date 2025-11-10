
CREATE TABLE IF NOT EXISTS `tours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `tours` (`title`, `location`, `description`, `price`, `duration`, `image_url`) VALUES
('Mt. Apo Adventure', 'Davao del Sur', 'Conquer the Philippines highest peak at 2,954 meters. Experience diverse ecosystems from rainforest to mossy forest, and witness breathtaking sunrise views.', 12500.00, '3 Days 2 Nights', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop'),
('Mt. Pulag Sea of Clouds', 'Benguet', 'Trek to the famous sea of clouds at Luzon highest peak. Marvel at the stunning sunrise above the clouds and explore the unique dwarf bamboo forest.', 8900.00, '2 Days 1 Night', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&h=600&fit=crop'),
('Lake Holon Trek', 'South Cotabato', 'Discover the pristine crater lake hidden in the mountains of South Cotabato. Crystal clear waters and stunning mountain scenery await.', 7500.00, '2 Days 1 Night', 'https://images.unsplash.com/photo-1439066615861-d1af74d74000?w=800&h=600&fit=crop'),
('Mt. Dulang-Dulang Expedition', 'Bukidnon', 'Challenge yourself with the Philippines 2nd highest mountain. Trek through mossy forests and enjoy spectacular mountain vistas.', 15000.00, '4 Days 3 Nights', 'https://images.unsplash.com/photo-1519904981063-b0cf448d479e?w=800&h=600&fit=crop'),
('Mt. Kitanglad Range', 'Bukidnon', 'Explore the Kitanglad Mountain Range Natural Park with its rich biodiversity and stunning highland landscapes.', 11200.00, '3 Days 2 Nights', 'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=800&h=600&fit=crop'),
('Mt. Kalatungan Trek', 'Bukidnon', 'Experience the wild beauty of Mt. Kalatungan with its unique rock formations and pristine mountain wilderness.', 10500.00, '3 Days 2 Nights', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop&sat=-10'),
('Mt. Guiting-Guiting Extreme', 'Sibuyan Island, Romblon', 'Conquer the Philippines most challenging mountain climb! Known as G2, this extreme technical climb features jagged peaks, knife-edge ridges, and breathtaking views. Only for experienced mountaineers.', 18000.00, '4 Days 3 Nights', 'https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=800&h=600&fit=crop');


CREATE TABLE IF NOT EXISTS `tour_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tour_id` int(11) DEFAULT NULL,
  `tour_title` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `number_of_people` int(11) DEFAULT 1,
  `total_price` decimal(10,2) NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `special_requests` text,
  `booking_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tour_id` (`tour_id`),
  CONSTRAINT `tour_bookings_ibfk_1` FOREIGN KEY (`tour_id`) REFERENCES `tours` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


SELECT COUNT(*) as 'Total Tours' FROM tours;
SELECT id, title, location, price, duration FROM tours ORDER BY price;
  
