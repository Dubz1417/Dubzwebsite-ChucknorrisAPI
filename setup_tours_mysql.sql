
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
('Mt. Apo Adventure', 'Davao del Sur', 'Conquer the Philippines highest peak at 2,954 meters. Experience diverse ecosystems from rainforest to mossy forest, and witness breathtaking sunrise views.', 12500.00, '3 Days 2 Nights', 'attached_assets/Mount APo_1761475071808.jpg'),
('Mt. Pulag Sea of Clouds', 'Benguet', 'Trek to the famous sea of clouds at Luzon highest peak. Marvel at the stunning sunrise above the clouds and explore the unique dwarf bamboo forest.', 8900.00, '2 Days 1 Night', 'attached_assets/Mount Pulag_1761475071808.jpg'),
('Lake Holon Trek', 'South Cotabato', 'Discover the pristine crater lake hidden in the mountains of South Cotabato. Crystal clear waters and stunning mountain scenery await.', 7500.00, '2 Days 1 Night', 'attached_assets/Lake Holon_1761475071807.jpg'),
('Mt. Dulang-Dulang Expedition', 'Bukidnon', 'Challenge yourself with the Philippines 2nd highest mountain. Trek through mossy forests and enjoy spectacular mountain vistas.', 15000.00, '4 Days 3 Nights', 'attached_assets/Mount Dulang Dulang_1761476035392.jpg'),
('Mt. Kitanglad Range', 'Bukidnon', 'Explore the Kitanglad Mountain Range Natural Park with its rich biodiversity and stunning highland landscapes.', 11200.00, '3 Days 2 Nights', 'attached_assets/Mount kitanglad_1761476035393.webp'),
('Mt. Kalatungan Trek', 'Bukidnon', 'Experience the wild beauty of Mt. Kalatungan with its unique rock formations and pristine mountain wilderness.', 10500.00, '3 Days 2 Nights', 'attached_assets/Mt Kalatungan_1761476035394.jpg'),
('Mt. Guiting-Guiting Extreme', 'Sibuyan Island, Romblon', 'Conquer the Philippines most challenging mountain climb! Known as G2, this extreme technical climb features jagged peaks, knife-edge ridges, and breathtaking views. Only for experienced mountaineers.', 18000.00, '4 Days 3 Nights', 'attached_assets/Mount Guiting Guiting_1761476110166.jpg');


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

  