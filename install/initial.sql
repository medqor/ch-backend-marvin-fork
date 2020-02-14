-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Generation Time: Nov 22, 2019 at 02:51 PM
-- Server version: 5.5.60-0+deb7u1-log
-- PHP Version: 7.0.33-0+deb9u6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
--

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE `actions` (
                           `id` int(11) NOT NULL,
                           `controller` int(3) NOT NULL,
                           `action` varchar(45) NOT NULL,
                           `status` tinyint(4) NOT NULL DEFAULT '1',
                           `datecreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           `path` varchar(255) NOT NULL DEFAULT '/',
                           `parent` tinyint(4) NOT NULL DEFAULT '1',
                           `active_by_default` tinyint(1) DEFAULT '1',
                           `nav_menu` tinyint(4) NOT NULL DEFAULT '1',
                           `icon` varchar(45) NOT NULL DEFAULT '',
                           `menu_order` int(11) NOT NULL DEFAULT '99'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `controllers`
--

CREATE TABLE `controllers` (
                               `id` int(11) NOT NULL,
                               `controller` varchar(45) NOT NULL,
                               `status` tinyint(4) NOT NULL DEFAULT '1',
                               `datecreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
                                   `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
                               `id` int(10) UNSIGNED NOT NULL,
                               `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions_roles`
--

CREATE TABLE `permissions_roles` (
                                     `id` int(10) UNSIGNED NOT NULL,
                                     `permission_id` int(11) NOT NULL,
                                     `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
                         `id` int(10) UNSIGNED NOT NULL,
                         `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
                        `id` int(10) UNSIGNED NOT NULL,
                        `first_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                        `last_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                        `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                        `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
                        `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                        `status` int(10) UNSIGNED DEFAULT NULL,
                        `username` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                        `level` int(2) DEFAULT '3'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `first_name`, `last_name`, `title`, `email`, `password`, `status`, `username`, `level`) VALUES
(119, 'Admin', 'Istrator', 'Admin', 'admin@php4.me', '117cOLq2KrZ9U', 1, 'admin', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_logins`
--

CREATE TABLE `user_logins` (
                               `id` int(10) UNSIGNED NOT NULL,
                               `user_id` int(10) UNSIGNED NOT NULL,
                               `status` int(10) UNSIGNED NOT NULL DEFAULT '0',
                               `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `ip` varchar(45) DEFAULT NULL,
                               `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `actions`
--
ALTER TABLE `actions`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `controllers`
--
ALTER TABLE `controllers`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
    ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indexes for table `permissions_roles`
--
ALTER TABLE `permissions_roles`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_logins`
--
ALTER TABLE `user_logins`
    ADD PRIMARY KEY (`id`),
    ADD KEY `user_id` (`user_id`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `actions`
--
ALTER TABLE `actions`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `controllers`
--
ALTER TABLE `controllers`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `permissions_roles`
--
ALTER TABLE `permissions_roles`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;
--
-- AUTO_INCREMENT for table `user_logins`
--
ALTER TABLE `user_logins`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=240;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
