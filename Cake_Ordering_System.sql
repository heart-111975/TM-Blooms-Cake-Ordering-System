-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2026 at 04:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Cake_Ordering_System`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `cake_name` varchar(100) NOT NULL,
  `size` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `pickup_date` date NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `contact_number`, `cake_name`, `size`, `quantity`, `price`, `pickup_date`, `status`, `created_at`) VALUES
(8, 'heart', '09673010043', 'Brownies', 'Other - To be discussed', 1, 200.00, '2026-11-08', 'Confirmed', '2026-11-05 01:58:28'),
(9, 'heart', '09673010043', 'Brownies', 'Other - To be discussed', 1, 200.00, '2026-11-08', 'Pending', '2026-11-05 02:07:55'),
(10, 'bru', '095551521', 'Chocolate Moist Cake', 'Other - To be discussed', 1, 400.00, '2026-11-07', 'Pending', '2026-11-05 02:11:27'),
(11, 'dan', '09673010043', 'Cookies', '8x4', 1, 192.00, '2026-11-21', 'Pending', '2026-11-05 02:13:32'),
(12, 'tifa heart', '+639673010043', 'Cookies', 'N/A', 2, 240.00, '2026-11-06', 'Pending', '2026-11-05 02:25:48'),
(13, 'tifa heart', '+639673010043', 'Cookies', 'N/A', 2, 240.00, '2026-11-06', 'Pending', '2026-11-05 02:28:44'),
(14, 'bry wilbur', '+639152498756', 'Classic Cake', 'N/A', 3, 1050.00, '2026-11-09', 'Confirmed', '2026-11-05 02:29:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
