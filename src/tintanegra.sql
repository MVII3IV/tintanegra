-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Sep 06, 2025 at 12:19 AM
-- Server version: 5.7.44
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tintanegra`
--

-- --------------------------------------------------------

--
-- Table structure for table `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `fechaInicio` date DEFAULT NULL,
  `fechaEntrega` date DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `anticipo` decimal(10,2) DEFAULT NULL,
  `tallas` json DEFAULT NULL,
  `imagenes` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pedidos`
--

INSERT INTO `pedidos` (`id`, `nombre`, `status`, `fechaInicio`, `fechaEntrega`, `costo`, `anticipo`, `tallas`, `imagenes`) VALUES
(1, 'erick', 'En producción', '2025-09-05', '2025-09-12', 1234.00, 12.00, '[{\"color\": \"#000000\", \"talla\": \"m\", \"cantidad\": 213}]', '[\"uploads/1757014725_porfolio (1).jpeg\"]'),
(2, 'Roberto', 'En producción', '2025-09-08', '2025-09-19', 2000.00, 1220.00, '[{\"color\": \"#f00000\", \"talla\": \"S\", \"cantidad\": 20}, {\"color\": \"#15b728\", \"talla\": \"L\", \"cantidad\": 17}]', '[\"uploads/1757016917_porfolio (2).jpeg\", \"uploads/1757016917_porfolio (3).jpeg\"]'),
(3, 'Estela', 'Pendiente', '2025-09-08', '2025-09-12', 3500.00, 500.00, '[{\"color\": \"#000000\", \"talla\": \"s\", \"cantidad\": 14}, {\"color\": \"#000000\", \"talla\": \"M\", \"cantidad\": 20}, {\"color\": \"#000000\", \"talla\": \"XL\", \"cantidad\": 2}]', '[\"uploads/1757016984_porfolio (9).jpeg\", \"uploads/1757016984_porfolio (10).jpeg\"]'),
(4, 'URBN', 'Pendiente', '2025-09-08', '2025-09-12', 1200.00, 600.00, '[{\"color\": \"#00e1ff\", \"talla\": \"AU-S\", \"cantidad\": 5}, {\"color\": \"#ff0000\", \"talla\": \"AU-M\", \"cantidad\": 12}, {\"color\": \"#00ff2a\", \"talla\": \"J-XS\", \"cantidad\": 12}]', '[\"uploads/1757035811_porfolio (4).jpeg\", \"uploads/1757035811_porfolio (5).jpeg\"]');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


ALTER TABLE pedidos
ADD COLUMN paletaColor VARCHAR(255) DEFAULT NULL;
