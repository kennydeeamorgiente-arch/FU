-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 04:35 AM
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
-- Database: `fusoms1`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_level`
--

CREATE TABLE `access_level` (
  `access_id` int(11) NOT NULL,
  `access_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `access_level`
--

INSERT INTO `access_level` (`access_id`, `access_name`) VALUES
(0, 'Student'),
(1, 'Club Adviser'),
(2, 'SAO'),
(3, 'OSL'),
(4, 'Vice Chancellor'),
(5, 'OUC');

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `activities_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `point_system_id` int(11) DEFAULT NULL,
  `type_of_activity` varchar(150) DEFAULT NULL,
  `nature_of_activity` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `collaboration`
--

CREATE TABLE `collaboration` (
  `coll_id` int(11) NOT NULL,
  `org_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `is_primary_organizer` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`) VALUES
(1, 'Computer Science Department'),
(2, 'Education Department'),
(3, 'Engineering Department');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `org_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `event_name` varchar(150) NOT NULL,
  `event_desc` text DEFAULT NULL,
  `event_start_date` date DEFAULT NULL,
  `event_end_date` date DEFAULT NULL,
  `event_start_time` time NOT NULL,
  `event_end_time` time NOT NULL,
  `event_purpose` text DEFAULT NULL,
  `event_uni_objectives` text DEFAULT NULL,
  `number_of_participants` int(11) DEFAULT NULL,
  `has_invited_speaker` tinyint(1) DEFAULT NULL,
  `name_of_invited_resource_speaker` varchar(150) DEFAULT NULL,
  `invited_speaker_description` varchar(255) DEFAULT NULL,
  `with_collaborators` tinyint(4) NOT NULL DEFAULT 0,
  `event_is_in_campus` tinyint(1) DEFAULT NULL,
  `event_venue` varchar(200) DEFAULT NULL,
  `event_budget` decimal(10,2) DEFAULT NULL,
  `source_of_funds` varchar(111) NOT NULL,
  `current_access_id` int(11) DEFAULT NULL,
  `highest_access_level` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `org_id`, `status_id`, `event_name`, `event_desc`, `event_start_date`, `event_end_date`, `event_start_time`, `event_end_time`, `event_purpose`, `event_uni_objectives`, `number_of_participants`, `has_invited_speaker`, `name_of_invited_resource_speaker`, `invited_speaker_description`, `with_collaborators`, `event_is_in_campus`, `event_venue`, `event_budget`, `source_of_funds`, `current_access_id`, `highest_access_level`, `created_at`) VALUES
(90, 0, 1, 'dada', NULL, '2025-12-10', '2025-12-16', '11:27:00', '15:33:00', 'wefbwef', 'webfwebfwebe', 32, 1, 'qwdqw', 'vwew', 1, 0, 'asdasd', 0.00, 'organization', 1, NULL, '2025-12-18 14:28:25'),
(91, 0, 1, 'dada', NULL, '2025-12-10', '2025-12-16', '11:27:00', '15:33:00', 'wefbwef', 'webfwebfwebe', 32, 1, 'qwdqw', 'vwew', 1, 0, 'asdasd', 0.00, 'organization', 1, NULL, '2025-12-18 14:41:35'),
(92, 0, 1, 'dada', NULL, '2025-12-10', '2025-12-16', '11:27:00', '15:33:00', 'wefbwef', 'webfwebfwebe', 32, 1, 'qwdqw', 'vwew', 1, 0, 'asdasd', 0.00, 'organization', 1, NULL, '2025-12-18 14:41:56'),
(93, 0, 1, 'dada', NULL, '2025-12-10', '2025-12-16', '11:27:00', '15:33:00', 'wefbwef', 'webfwebfwebe', 32, 1, 'qwdqw', 'vwew', 1, 0, 'asdasd', 0.00, 'organization', 1, NULL, '2025-12-18 14:42:00'),
(94, 1, 1, 'w', NULL, '2025-12-01', '2025-12-17', '10:51:00', '12:51:00', 'qf', 'wf', 3, 1, 'ffff', 'wfff', 1, 1, 'e', 0.00, 'organization', 1, NULL, '2025-12-18 14:53:55'),
(95, 11, 7, 'testEVENT', NULL, '2025-12-01', '2025-12-04', '01:07:00', '12:08:00', 'trbrtgher', 'wegfwefw', 100, 1, 'sf', 'asbdasdsf', 1, 0, 'switzerland', 29.00, 'university', 0, 2, '2025-12-22 03:03:54');

-- --------------------------------------------------------

--
-- Table structure for table `events_history`
--

CREATE TABLE `events_history` (
  `event_history_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `events_history`
--

INSERT INTO `events_history` (`event_history_id`, `user_id`, `event_id`, `remarks`, `created_at`, `status_id`) VALUES
(118, 20, 95, 'practice', '2025-12-22 03:17:26', 7);

-- --------------------------------------------------------

--
-- Table structure for table `events_status`
--

CREATE TABLE `events_status` (
  `status_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `events_status`
--

INSERT INTO `events_status` (`status_id`, `name`) VALUES
(1, 'Pending'),
(2, 'In-Progress'),
(3, 'Awaiting Documentation'),
(4, 'For Verification'),
(5, 'Completed'),
(6, 'Rejected'),
(7, 'Returned For Revision'),
(8, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `event_budget_breakdown`
--

CREATE TABLE `event_budget_breakdown` (
  `budget_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `description` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `purpose` varchar(200) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_budget_breakdown`
--

INSERT INTO `event_budget_breakdown` (`budget_id`, `event_id`, `description`, `quantity`, `unit`, `purpose`, `unit_price`, `amount`, `created_at`) VALUES
(75, 90, 'webfweb', 343, '34324', '4', 4.00, 0.00, '2025-12-18 14:28:25'),
(76, 90, '4', 4, '4', '4', 4.00, 4.00, '2025-12-18 14:28:25'),
(77, 91, 'webfweb', 343, '34324', '4', 4.00, 0.00, '2025-12-18 14:41:35'),
(78, 91, '4', 4, '4', '4', 4.00, 4.00, '2025-12-18 14:41:35'),
(79, 92, 'webfweb', 3, '3', '4', 4.00, 0.00, '2025-12-18 14:41:56'),
(80, 92, '4', 4, '4', '4', 4.00, 4.00, '2025-12-18 14:41:56'),
(81, 93, 'webfweb', 3, '3', '4', 4.00, 0.00, '2025-12-18 14:42:00'),
(82, 93, '4', 4, '4', '4', 4.00, 4.00, '2025-12-18 14:42:00'),
(83, 94, '2', 2, '2', '2', 2.00, 0.00, '2025-12-18 14:53:55'),
(84, 95, 'wefewf', 2, '2', '2', 2.00, 4.00, '2025-12-22 03:03:54'),
(85, 95, 'dad', 5, '5', '5', 5.00, 25.00, '2025-12-22 03:03:54');

-- --------------------------------------------------------

--
-- Table structure for table `event_documentation`
--

CREATE TABLE `event_documentation` (
  `doc_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('Documentation','Photo','Video','Other') NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_uploads`
--

CREATE TABLE `event_uploads` (
  `upload_id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` enum('Proposal','Program Paper','Communication Letter') NOT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_uploads`
--

INSERT INTO `event_uploads` (`upload_id`, `org_id`, `event_id`, `file_name`, `file_path`, `file_type`, `uploaded_at`) VALUES
(11, 1, 94, 'c7e25941503b43ccb5f013b6c51f8310.jpg', '1766069635_e1b56bcd0372ae50d440.jpg', 'Program Paper', '2025-12-18 22:53:55'),
(12, 11, 95, 'Program Paper', 'https://github.com/allengabriellevillas-lab/FoundationUEMS-master/tree/dev', 'Program Paper', '2025-12-22 11:03:54');

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

CREATE TABLE `organization` (
  `org_id` int(11) NOT NULL,
  `org_name` varchar(150) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `adviser` varchar(150) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `org_type_id` int(11) DEFAULT NULL,
  `facebook_link` varchar(80) DEFAULT NULL,
  `logo` varchar(255) DEFAULT 'foundationu_logo.png',
  `org_num_members` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`org_id`, `org_name`, `description`, `adviser`, `status`, `org_type_id`, `facebook_link`, `logo`, `org_num_members`, `deleted_at`, `created_at`) VALUES
(1, 'Techy Cluby', 'A student organization focusing on technology and innovation.', 'Prof. John Reyes', 'Active', 1, 'kieannejames.paco@facebook.com', 'foundationu_logo.png', 14, NULL, '2025-11-11 15:46:15'),
(2, 'Cultural Arts Society', 'Promotes cultural awareness and appreciation.', 'Prof. Maria Santos', 'Active', 3, 'allenbuang@facebook.com', 'foundationu_logo.png', 12, NULL, '2025-11-11 15:46:15'),
(3, 'Sports Excellence Team', 'Encourages sportsmanship and physical fitness.', 'Coach Liza Cruz', 'Active', 4, 'disneyprincess@facebook.com', 'foundationu_logo.png', 30, NULL, '2025-11-11 15:46:15'),
(7, 'Agora Society', 'The Foundation University Agora Society is a student organization that promotes freedom, open discussion, and critical thinking. Inspired by the ancient Greek Agora, it provides a space for students to share ideas, learn from one another, and explore the principles of classical liberalism, tech liberty, and free markets. The Society aims to build a community of responsible, independent, and principled student leaders who value excellence, integrity, and service.', 'Kimberly Paez', NULL, 2, '', 'agora_society_6913ff8cef2be.png', 34, NULL, '2025-11-12 03:31:25'),
(8, 'Agora Society', 'Na', 'Prof. John Reyes', NULL, 1, '', 'agora_society_692061ca7bc0b.png', 3, NULL, '2025-11-21 12:57:46'),
(9, 'test org', 'afafsaf', 'test', NULL, 1, 'sadas', 'test_org_6948b1fcd32a5.jpg', 8, '2025-12-22 02:54:42', '2025-12-22 02:50:36'),
(10, 'test org', 'afafsaf', 'test', NULL, 1, 'sadas', 'test_org_6948b1fd01554.jpg', 8, '2025-12-22 02:54:46', '2025-12-22 02:50:37'),
(11, 'test', 'asbdasbdb', 'testadviser', NULL, 1, 'asdbasbas', 'test_6948b4524454f.jpg', 10, NULL, '2025-12-22 03:00:34');

-- --------------------------------------------------------

--
-- Table structure for table `organization_type`
--

CREATE TABLE `organization_type` (
  `org_type_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `organization_type`
--

INSERT INTO `organization_type` (`org_type_id`, `type`) VALUES
(1, 'Academic'),
(2, 'Non-Academic'),
(3, 'Cultural'),
(4, 'Sports'),
(5, 'Religious');

-- --------------------------------------------------------

--
-- Table structure for table `point_system`
--

CREATE TABLE `point_system` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `points` int(11) DEFAULT 0,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `point_system`
--

INSERT INTO `point_system` (`id`, `description`, `points`, `timestamp`) VALUES
(4, 'Sponsored activity by the organization outside the University', 100, '2025-11-20 12:21:28'),
(5, 'Co-sponsored activity in collaboration with NGOs / Industry partners outside the University', 100, '2025-11-20 12:21:28'),
(6, 'Sponsored activity by the organization within the University', 80, '2025-11-20 12:21:28'),
(7, 'Co-sponsored activity in collaboration with NGOs / Industry partners within the University', 80, '2025-11-20 12:21:28'),
(8, 'Sponsored activity by the organization within their respective department', 60, '2025-11-20 12:21:28'),
(9, 'Co-sponsored activity in collaboration with other departments', 60, '2025-11-20 12:21:28'),
(10, 'Participation to other department’s activity in the university', 30, '2025-11-20 12:21:28'),
(11, 'Participation to own activity within the university', 10, '2025-11-20 12:21:28'),
(12, 'General Audience', 10, '2025-11-20 12:21:37'),
(13, 'Participated as a Contestant of a competition organized by FUSG', 30, '2025-11-20 12:21:37'),
(14, 'Won Competition of an SG Organized Activity', 50, '2025-11-20 12:21:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(55) NOT NULL,
  `last_name` varchar(55) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `access_id` int(11) DEFAULT NULL,
  `org_id` int(11) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `deleted_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `position`, `access_id`, `org_id`, `created_at`, `deleted_at`) VALUES
(1, '', '', 'admin@fusg.edu', '$2a$12$521lIxfL3GAn/pJUqwCFeekNiziFwF4xyhIiB75Ci9WT9/3HTE/B2', 'Chancellor', 1, NULL, '2025-11-08', NULL),
(2, '', '', 'john.adviser@fusg.edu', 'adviser123', 'Vice-Chancellor', 2, NULL, '2025-11-08', '2025-12-11'),
(3, '', '', 'mark.leader@fusg.edu', 'leader123', 'Head of OSL', 3, NULL, '2025-11-08', '2025-12-11'),
(4, '', '', 'ella.culture@fusg.edu', 'leader123', 'Director of Programs', 3, NULL, '2025-11-08', '2025-12-11'),
(5, '', '', 'mike.sports@fusg.edu', 'leader123', 'Agora Adviser', 4, NULL, '2025-11-08', '2025-12-11'),
(6, '', '', 'johnnyxavier.obar@foundationu.com', '$2y$10$SQj5OmerSvYLR7Oge6CaQ.YXt9zvgwz0xh5KsDVf5F7GVotIFq4a.', 'President', 5, 7, '2025-11-16', '2025-12-11'),
(7, '', '', 'student@gmail.com', '$2y$10$6oMNsfFKw0t//CQF4bQJ5uGTmuVf6ASIIuIiZ9EL0N0USQ4FauuLu', 'President', 0, 1, '2025-12-11', NULL),
(8, '', '', 'clubadviser@gmail.com', '$2y$10$ejmXdO3Ve0dvnYTrJnl0vO8uzOf0Fwm3Mc2lHC98hgveX/6mnuM/O', 'Secretary', 1, 7, '2025-12-11', NULL),
(9, '', '', 'sao@gmail.com', '$2y$10$5SfZsTrUNIvfuyTOeGrDAewNeD7NgxFWgAFIcTbieqhRFdpzFbdKC', 'SG Adviser', 2, NULL, '2025-12-11', NULL),
(10, '', '', 'osl@gmail.com', '$2y$10$X..TjcwhqErDJOGtIxsEwu4s4jTyQt9U6qH1nR9ILRoWA2QFfLtk6', 'OSL Head', 3, NULL, '2025-12-11', NULL),
(11, '', '', 'vc@gmail.com', '$2y$10$G6Q.DFcmlj8iWMutPxUD6eJNricyNyFbsxOTuc6uqNBVKBlJtebj2', 'Board Director', 4, NULL, '2025-12-11', NULL),
(12, '', '', 'ouc@gmail.com', '$2y$10$XwOR1GSy4flE./xibiwRpuly7DGRikkX/SvDJlhUiM5rTzLD2NXvi', 'Chancellor', 5, NULL, '2025-12-11', NULL),
(13, 'kieannejames', 'paco', 'kieannejames.paco@foundationu.com', '$2y$10$1E9altufm3eTIzEfKi1FcOUnKuOj3YM7EKTEGbGctJszZMcAcAeR2', 'Administrator', 4, NULL, '2025-12-15', NULL),
(14, 'kieanne', '', 'kieannejamespaco@gmail.com', '$2y$10$rjf4AINccSWckG7JrlfliezWSlZhasmeRYHkgPgwunbsL4GgDVFFW', 'Administrator', 4, NULL, '2025-12-15', NULL),
(15, 'dada', 'dadad', 'dada@gmail.com', '$2y$10$uhgUDpJUfLobms7sbaVle.XkSGLX3wiGKSR/tozigOA9ZbgmIfbMC', 'Administrator', 0, NULL, '2025-12-15', NULL),
(16, 'xaxa', 'xaxa', 'xaxa@gmail.com', '$2y$10$2q1x6IysrlGUTMXNAU1Mue8oF/4nDgRm6HoVZO/4pKMDMMZJHFokq', 'Administrator', 0, NULL, '2025-12-15', NULL),
(17, 'baba', 'baba', 'baba@gmail.com', '$2y$10$uObQVmczHMbFxP3HC5zMAeQh2cYjYRAGuS18vP9nqRGNqTdi9chyu', 'none', 0, NULL, '2025-12-15', NULL),
(18, 'nana', 'nana', 'nana@gmail.com', '$2y$10$eKfqUvYUqdr0Wo7mmCW5iuBQUu/UEGhhrRuIjTK9eTkbkMAYONv7i', 'Administrator', 0, NULL, '2025-12-15', NULL),
(19, '', '', 'user@gmail.com', '$2y$10$enZjsLWSpryyPLqMLs9exe0TdWb0lMxw8MwLBoHwfhFe5VWWNxMuW', 'Administrator', 0, NULL, '2025-12-18', NULL),
(20, '', '', 'testadviser@gmail.com', '$2y$10$F6BXQob9nF8sPXihv6y5KejSS7cqRe63Ga7EOwquGTxSo68Si0/5i', 'adviser', 1, 11, '2025-12-22', NULL),
(21, '', '', 'testuser@gmail.com', '$2y$10$eur1P97rBnnqZMOWckwWDutWfzkQnVFtQDiqKDmDzKD8joTBO/fGi', 'member', 0, 11, '2025-12-22', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_level`
--
ALTER TABLE `access_level`
  ADD PRIMARY KEY (`access_id`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activities_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `event_id_2` (`event_id`),
  ADD KEY `point_system_id` (`point_system_id`),
  ADD KEY `event_id_3` (`event_id`),
  ADD KEY `point_system_id_2` (`point_system_id`);

--
-- Indexes for table `collaboration`
--
ALTER TABLE `collaboration`
  ADD PRIMARY KEY (`coll_id`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `events_id` (`event_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `org_id_2` (`org_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `org_id` (`org_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `org_id_2` (`org_id`),
  ADD KEY `org_id_3` (`org_id`),
  ADD KEY `current_access_id` (`current_access_id`),
  ADD KEY `highest_access_level` (`highest_access_level`),
  ADD KEY `status_id_2` (`status_id`),
  ADD KEY `org_id_4` (`org_id`),
  ADD KEY `current_access_id_2` (`current_access_id`),
  ADD KEY `highest_access_level_2` (`highest_access_level`),
  ADD KEY `status_id_3` (`status_id`);

--
-- Indexes for table `events_history`
--
ALTER TABLE `events_history`
  ADD PRIMARY KEY (`event_history_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id_2` (`user_id`),
  ADD KEY `event_id_2` (`event_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `user_id_3` (`user_id`),
  ADD KEY `event_id_3` (`event_id`),
  ADD KEY `status_id_2` (`status_id`);

--
-- Indexes for table `events_status`
--
ALTER TABLE `events_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `event_budget_breakdown`
--
ALTER TABLE `event_budget_breakdown`
  ADD PRIMARY KEY (`budget_id`),
  ADD KEY `fk_event` (`event_id`);

--
-- Indexes for table `event_documentation`
--
ALTER TABLE `event_documentation`
  ADD PRIMARY KEY (`doc_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `fk_doc_org` (`org_id`);

--
-- Indexes for table `event_uploads`
--
ALTER TABLE `event_uploads`
  ADD PRIMARY KEY (`upload_id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `fk_event_uploads_org` (`org_id`);

--
-- Indexes for table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`org_id`),
  ADD KEY `org_type_id` (`org_type_id`);

--
-- Indexes for table `organization_type`
--
ALTER TABLE `organization_type`
  ADD PRIMARY KEY (`org_type_id`);

--
-- Indexes for table `point_system`
--
ALTER TABLE `point_system`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `access_id` (`access_id`),
  ADD KEY `org_id` (`org_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_level`
--
ALTER TABLE `access_level`
  MODIFY `access_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `activities_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `collaboration`
--
ALTER TABLE `collaboration`
  MODIFY `coll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `events_history`
--
ALTER TABLE `events_history`
  MODIFY `event_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `events_status`
--
ALTER TABLE `events_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `event_budget_breakdown`
--
ALTER TABLE `event_budget_breakdown`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `event_documentation`
--
ALTER TABLE `event_documentation`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_uploads`
--
ALTER TABLE `event_uploads`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `organization`
--
ALTER TABLE `organization`
  MODIFY `org_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `organization_type`
--
ALTER TABLE `organization_type`
  MODIFY `org_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `point_system`
--
ALTER TABLE `point_system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `activities_point_system_fk` FOREIGN KEY (`point_system_id`) REFERENCES `point_system` (`id`);

--
-- Constraints for table `collaboration`
--
ALTER TABLE `collaboration`
  ADD CONSTRAINT `collaboration_ibfk_1` FOREIGN KEY (`org_id`) REFERENCES `organization` (`org_id`),
  ADD CONSTRAINT `collaboration_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

--
-- Constraints for table `events_history`
--
ALTER TABLE `events_history`
  ADD CONSTRAINT `events_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `events_history_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`),
  ADD CONSTRAINT `events_history_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `events_status` (`status_id`);

--
-- Constraints for table `event_budget_breakdown`
--
ALTER TABLE `event_budget_breakdown`
  ADD CONSTRAINT `fk_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_documentation`
--
ALTER TABLE `event_documentation`
  ADD CONSTRAINT `fk_doc_org` FOREIGN KEY (`org_id`) REFERENCES `organization` (`org_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_doc` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_uploads`
--
ALTER TABLE `event_uploads`
  ADD CONSTRAINT `fk_event_upload` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_uploads_org` FOREIGN KEY (`org_id`) REFERENCES `organization` (`org_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `organization`
--
ALTER TABLE `organization`
  ADD CONSTRAINT `organization_ibfk_1` FOREIGN KEY (`org_type_id`) REFERENCES `organization_type` (`org_type_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`access_id`) REFERENCES `access_level` (`access_id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`org_id`) REFERENCES `organization` (`org_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
