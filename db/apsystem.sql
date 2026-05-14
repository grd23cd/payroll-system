-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- TABLE: admin
-- --------------------------------------------------------

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `created_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `admin`
(`id`, `username`, `password`, `firstname`, `lastname`, `photo`, `created_on`)
VALUES
(1, 'nurhodelta', '$2y$10$fCOiMky4n5hCJx3cpsG20Od4wHtlkCLKmO6VLobJNRIg9ooHTkgjK',
'Neovic', 'Devierte', 'facebook-profile-image.jpeg', '2018-04-30');

-- --------------------------------------------------------
-- TABLE: attendance
-- --------------------------------------------------------

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_in` time NOT NULL,
  `status` int(1) NOT NULL,
  `time_out` time NOT NULL,
  `num_hr` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
-- TABLE: cashadvance
-- --------------------------------------------------------

CREATE TABLE `cashadvance` (
  `id` int(11) NOT NULL,
  `date_advance` date NOT NULL,
  `employee_id` varchar(15) NOT NULL,
  `amount` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
-- TABLE: deductions
-- --------------------------------------------------------

CREATE TABLE `deductions` (
  `id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL,
  `amount` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `deductions`
(`id`, `description`, `amount`)
VALUES
(1, 'SSS', 100),
(2, 'Pagibig', 150),
(3, 'PhilHealth', 150);

-- --------------------------------------------------------
-- TABLE: personal_deductions
-- --------------------------------------------------------

CREATE TABLE `personal_deductions` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(15) NOT NULL,
  `description` varchar(100) NOT NULL,
  `amount` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `personal_deductions`
(`id`, `employee_id`, `description`, `amount`)
VALUES
(1, 'ABC123456789', 'Laptop Loan', 500),
(2, 'ABC123456789', 'Uniform', 300),
(3, 'JIE625973480', 'Cash Shortage', 200);

-- --------------------------------------------------------
-- TABLE: employees
-- --------------------------------------------------------

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(15) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `birthdate` date NOT NULL,
  `contact_info` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `position_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `photo` varchar(200) NOT NULL,
  `created_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `employees`
(`id`, `employee_id`, `firstname`, `lastname`, `address`,
`birthdate`, `contact_info`, `gender`, `position_id`,
`schedule_id`, `photo`, `created_on`)
VALUES
(1, 'ABC123456789', 'Neovic', 'Devierte',
'Brgy. Mambulac, Silay City',
'2018-04-02', '09092735719', 'Male',
1, 2, 'desktop.jpg', '2018-04-28'),

(3, 'DYE473869250', 'Julyn', 'Divinagracia',
'E.B. Magalona',
'1992-05-02', '09123456789', 'Female',
2, 2, '', '2018-04-30'),

(4, 'JIE625973480', 'Gemalyn', 'Cepe',
'Carmen, Bohol',
'1995-10-02', '09468029840', 'Female',
2, 3, '', '2018-04-30');

-- --------------------------------------------------------
-- TABLE: overtime
-- --------------------------------------------------------

CREATE TABLE `overtime` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(15) NOT NULL,
  `hours` double NOT NULL,
  `rate` double NOT NULL,
  `date_overtime` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------
-- TABLE: position
-- --------------------------------------------------------

CREATE TABLE `position` (
  `id` int(11) NOT NULL,
  `description` varchar(150) NOT NULL,
  `rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `position`
(`id`, `description`, `rate`)
VALUES
(1, 'Programmer', 100),
(2, 'Writer', 50);

-- --------------------------------------------------------
-- TABLE: schedules
-- --------------------------------------------------------

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `time_in` time NOT NULL,
  `time_out` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `schedules`
(`id`, `time_in`, `time_out`)
VALUES
(1, '07:00:00', '16:00:00'),
(2, '08:00:00', '17:00:00'),
(3, '09:00:00', '18:00:00'),
(4, '10:00:00', '19:00:00');

-- --------------------------------------------------------
-- PRIMARY KEYS
-- --------------------------------------------------------

ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `cashadvance`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `personal_deductions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `overtime`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `position`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------
-- AUTO_INCREMENT
-- --------------------------------------------------------

ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `cashadvance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `personal_deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `overtime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

COMMIT;