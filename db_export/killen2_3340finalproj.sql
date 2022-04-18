-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 18, 2022 at 01:54 PM
-- Server version: 10.4.24-MariaDB-log
-- PHP Version: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `killen2_3340finalproj`
--

-- --------------------------------------------------------

--
-- Table structure for table `CART_ITEMS`
--

CREATE TABLE `CART_ITEMS` (
  `id` int(8) UNSIGNED NOT NULL,
  `item` int(8) UNSIGNED NOT NULL,
  `user` int(8) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `CATEGORIES`
--

CREATE TABLE `CATEGORIES` (
  `id` int(8) UNSIGNED NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `CATEGORIES`
--

INSERT INTO `CATEGORIES` (`id`, `name`) VALUES
(1, 'Uncategorized'),
(7, 'Flowers'),
(8, 'Crops & Herbs'),
(9, 'Miscellaneous Plants'),
(10, 'Gardening Equipment'),
(11, 'Seeds');

-- --------------------------------------------------------

--
-- Table structure for table `ITEMS`
--

CREATE TABLE `ITEMS` (
  `id` int(8) UNSIGNED NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `manufacturer` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(10,2) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `category` int(8) UNSIGNED NOT NULL,
  `imgpath` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ITEMS`
--

INSERT INTO `ITEMS` (`id`, `name`, `description`, `manufacturer`, `price`, `quantity`, `category`, `imgpath`) VALUES
(26, 'Alcea', 'Alcea is a genus of over 80 species of flowering plants in the mallow family Malvaceae, commonly known as the hollyhocks. They are native to Asia and Europe.', 'Windsor Garden Shop', '8.25', 20, 7, 'img/Alcea.jpg'),
(27, 'Anagallis', 'Anagallis is a genus of about 20–25 species of flowering plants in the family Primulaceae, commonly called pimpernel.', 'Windsor Garden Shop', '6.00', 15, 7, 'img/Anagallis.jpg'),
(28, 'Areca', 'Areca is a genus of 51 species of palms in the family Arecaceae, found in humid tropical forests from the islands of the Philippines, Malaysia and India, across Southeast Asia to Melanesia.', 'Windsor Garden Shop', '7.75', 12, 9, 'img/Areca.jpg'),
(29, 'Bamboo', 'Bamboos are a diverse group of evergreen perennial flowering plants in the subfamily Bambusoideae of the grass family Poaceae.', 'Flower Mart', '3.49', 25, 9, 'img/Bamboo.jpg'),
(30, 'Bonsai', 'Coniferous trees cared for in the style of the bonsai tradition by our expert nursery staff. With regular trimmings and general plant care, these trees make an excellent decorative piece.', 'Windsor Garden Shop', '6.00', 10, 9, 'img/Bonsai.jpg'),
(31, 'Cactus', 'A cactus is a member of the plant family Cactaceae, a family comprising about 127 genera with some 1750 known species of the order Caryophyllales.', 'Ontario Flowers', '3.00', 15, 9, 'img/Cactus.jpg'),
(32, 'Calanit', 'The calanit is probably the most famous flower in Israel especially as in 2013 it was crowned as Israel’s national flower.', 'Windsor Garden Shop', '8.87', 12, 7, 'img/Calanit.jpg'),
(33, 'Centaurea', 'Centaurea is a genus of over 700 species of herbaceous thistle-like flowering plants in the family Asteraceae. Members of the genus are found only north of the equator, mostly in the Eastern Hemisphere.', 'Ontario Flowers', '4.25', 20, 7, 'img/Centaurea.jpg'),
(34, 'Chartzit', 'Chartzit are Israel chrysanthemums, sometimes called mums or chrysanths, are flowering plants of the genus Chrysanthemum in the family Asteraceae. ', 'Windsor Garden Shop', '7.00', 10, 7, 'img/Chartzit.jpg'),
(35, 'Eggplant', 'Eggplant, aubergine or brinjal is a plant species in the nightshade family Solanaceae. Solanum melongena is grown worldwide for its edible fruit. Most commonly purple, the spongy, absorbent fruit is used in several cuisines.', 'Flower Mart', '8.25', 25, 8, 'img/Eggplant.jpg'),
(36, 'Fern', 'A fern is a member of a group of vascular plants that reproduce via spores and have neither seeds nor flowers.', 'Flower Mart', '3.00', 15, 9, 'img/Fern.jpg'),
(37, 'Hibiscus', 'Hibiscus is a genus of flowering plants in the mallow family, Malvaceae. The genus is quite large, comprising several hundred species that are native to warm temperate, subtropical and tropical regions throughout the world.', 'Flower Mart', '9.21', 20, 7, 'img/Hibiscus.jpg'),
(38, 'Iris', 'Iris is a genus of 260–300 species of flowering plants with showy flowers. It takes its name from the Greek word for a rainbow, which is also the name for the Greek goddess of the rainbow, Iris.', 'Windsor Garden Shop', '1.00', 25, 7, 'img/Iris.jpg'),
(39, 'Linum', 'Linum (flax) is a genus of approximately 200 species in the flowering plant family Linaceae. They are native to temperate and subtropical regions of the world.', 'Windsor Garden Shop', '7.25', 12, 7, 'img/Linum.jpg'),
(40, 'Lupinus', 'Lupinus, commonly known as lupin, lupine, or regionally bluebonnet etc., is a genus of flowering plants in the legume family Fabaceae. ', 'Windsor Garden Shop', '8.99', 10, 7, 'img/Lupinus.jpg'),
(41, 'Mint', 'Mentha (Mint) is a genus of plants in the family Lamiaceae. The exact distinction between species is unclear; it is estimated that 13 to 24 species exist. ', 'Ontario Flowers', '1.75', 30, 8, 'img/Mint.jpg'),
(42, 'Narcissus', 'Narcissus is a genus of predominantly spring flowering perennial plants of the amaryllis family, Amaryllidaceae. Various common names including daffodil, narcissus, and jonquil are used to describe all or some members of the genus.', 'Ontario Flowers', '8.00', 15, 7, 'img/Narcissus.jpg'),
(43, 'Ophrys', 'The genus Ophrys is a large group of orchids from the alliance Orchis in the subtribe Orchidinae. They are widespread across much of Europe, North Africa, Caucasus, the Canary Islands, and the Middle East.', 'Windsor Garden Shop', '7.50', 25, 7, 'img/Ophrys.jpg'),
(44, 'Oregano', 'Oregano is a culinary herb, used for the flavour of its leaves, which can be more intense when dried than fresh.', 'Windsor Garden Shop', '9.00', 30, 8, 'img/Oregano.jpg'),
(45, 'Oxalis', 'Oxalis is a large genus of flowering plants in the wood-sorrel family Oxalidaceae, comprising over 550 species.', 'Flower Mart', '8.50', 20, 7, 'img/Oxalis.jpg'),
(46, 'Peppermint', 'Peppermint (Mentha × piperita, also known as Mentha balsamea Wild) is a hybrid mint, a cross between watermint and spearmint.', 'Flower Mart', '5.25', 20, 8, 'img/Peppermint.jpg'),
(47, 'Petunia', 'Petunia is genus of 20 species of flowering plants of South American origin.', 'Flower Mart', '8.00', 35, 7, 'img/Petunia.jpg'),
(48, 'Plumeria', 'Plumeria, known as frangipani, is a genus of flowering plants in the subfamily Rauvolfioideae, of the family Apocynaceae.', 'Flower Mart', '9.25', 15, 7, 'img/Plumeria.jpg'),
(49, 'Rakefet', 'Rakefet or Cyclamen is a genus of 23 species of perennial flowering plants in the family Primulaceae.', 'Windsor Garden Shop', '9.25', 12, 7, 'img/Rakefet.jpg'),
(50, 'Rose', 'A rose is a woody perennial flowering plant of the genus Rosa, in the family Rosaceae, or the flower it bears. There are over three hundred species and tens of thousands of cultivars.', 'Windsor Garden Shop', '3.00', 30, 7, 'img/Rose.jpg'),
(51, 'Sage', 'Salvia officinalis, the common sage or just sage, is a perennial, evergreen subshrub, with woody stems, grayish leaves, and blue to purplish flowers.', 'Windsor Garden Shop', '9.00', 15, 8, 'img/Sage.jpg'),
(52, 'Samanea', 'Samanea is a genus of flowering plants in the family Fabaceae. It belongs to the mimosoid clade of the subfamily Caesalpinioideae.', 'Windsor Garden Shop', '5.49', 10, 7, 'img/Samanea.jpg'),
(53, 'Snake', 'Dracaena trifasciata is a species of flowering plant in the family Asparagaceae, native to tropical West Africa from Nigeria east to the Congo. It is most commonly known as the snake plant.', 'Flower Mart', '4.00', 5, 7, 'img/Snake.jpg'),
(54, 'Spider Plant', 'Chlorophytum comosum, usually called spider plant but also known as spider ivy, ribbon plant, and hen and chickens is a species of evergreen perennial flowering plant. It is native to tropical and southern Africa.', 'Flower Mart', '2.00', 12, 7, 'img/Spider.jpg'),
(55, 'Sunflower', 'Helianthus is a genus comprising about 70 species of annual and perennial flowering plants in the daisy family Asteraceae commonly known as sunflowers.', 'Ontario Flowers', '1.75', 30, 7, 'img/Sunflower.jpg'),
(56, 'Tulip', 'Tulips are a genus of spring-blooming perennial herbaceous bulbiferous geophytes. The flowers are usually large, showy and brightly colored, generally red, pink, yellow, or white.', 'Ontario Flowers', '3.10', 35, 7, 'img/Tulip.jpg'),
(57, 'Pruning Shears', 'Pruning shears, also called hand pruners, or secateurs, are a type of scissors for use on plants. ', 'Fiskars', '20.98', 10, 10, 'img/PruningShears.jpg'),
(58, 'Watering Can', 'A portable container for water with a handle and funnel, used to water plants by hand.', 'DCN', '7.98', 15, 10, 'img/WateringCan.jpg'),
(59, 'Shovel', 'A tool with a broad blade and a medium-length handle. Typically used for digging, moving, and lifting materials like soil and gravel.  ', 'Anvil', '15.98', 12, 10, 'img/Shovel.jpg'),
(60, 'Square Tip Shovel', 'The square tip shovel can hold a much more significant amount of loose material than a regular shovel.', 'Anvil ', '21.77', 8, 10, 'img/SquareShovel.jpg'),
(61, 'Steel Wheelbarrow', 'A small, hand propelled cart used for transporting items like bricks, soil, and garden debris. It has a singular wheel in the front, as well as two handles and supporting legs in the rear. ', 'Erie', '159.00', 5, 10, 'img/SteelWheelbarrow.jpg'),
(62, 'Gloves', 'Garments used to cover hands. In gardening, they are used to protect against dirt, scratches, and blisters.', 'Firm Grip', '9.98', 20, 10, 'img/Gloves.jpg'),
(63, 'Gardening Hose', 'A flexible, hollow tube conveying water. Used chiefly for watering plants. ', 'Swan', '27.48', 20, 10, 'img/GardeningHose.jpg'),
(64, 'Coiled Landscape Edging', 'Used to divide outdoor living areas into different sections. Sometimes used as a decorative element. ', 'Vigoro', '28.98', 50, 10, 'img/LandscapeEdging.jpg'),
(65, 'Potting Soil', 'A mixture of various nutrients used as a growing medium for plants grown in containers.', 'Miracle-Gro', '14.47', 45, 10, 'img/PottingSoil.jpg'),
(66, 'Lawn Fertilizer', 'A substance, synthetic or natural, that is applied to soil in order to increase fertility and encourage plant growth. ', 'Vigoro', '8.67', 50, 10, 'img/LawnFertilizer.jpg'),
(67, 'Nursery Pots', 'A plastic pot for your plant to grow in.  It has plenty of space for its roots and the pot has holes in the bottom for water to drain.', 'Viagrow', '29.98', 35, 10, 'img/NurseryPots.jpg'),
(68, 'Garden Planter', 'A box for plants, used to raise and grow plants in a variety of locations not limited to the ground.', 'Grapevine', '139.00', 10, 10, 'img/GardenPlanter.jpg'),
(69, 'Flower Seeds', 'A 10-Pack of varying flower seeds.', 'Burpee', '22.98', 60, 11, 'img/FlowerSeeds.jpg'),
(70, 'Vegetable Seeds', 'A 10-Pack of varying vegetable seeds.', 'Burpee', '26.98', 55, 11, 'img/VegetableSeeds.jpg'),
(71, 'Herb Seeds', 'A 10-Pack of varying herbs seeds.', 'Burpee', '20.98', 50, 11, 'img/HerbSeeds.jpg'),
(72, 'Rake', 'A tool with a pole and crossboar like a comb at the end, used for drawing together cut grass or fallen leaves, or smoothing loose soil or gravel.', 'Anvil', '15.98', 15, 10, 'img/Rake.jpg'),
(73, 'Trowel', 'A small handheld tool with a curved scoop for lifting plants or earth.', 'Fiskars', '10.38', 30, 10, 'img/Trowel.jpg'),
(74, 'Hoe', 'A long-handled gardening tool with a thin metal blade, used mainly for weeding and breaking up soil.', 'Garant', '67.12', 10, 10, 'img/Hoe.jpg'),
(75, 'Sprinker', 'A device used to spray water.  They are used to water plants or grass.', 'RAIN BIRD', '7.83', 40, 10, 'img/Sprinkler.jpg'),
(76, 'Weeding Knife', 'A thin, sharp-angled knife that\'s designed to reach into nooks and crannies, slice the weeds off at the roots and then drag the debris out for easy disposal.', 'Nisaku', '39.98', 15, 10, 'img/WeedingKnife.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `ORDERS`
--

CREATE TABLE `ORDERS` (
  `id` int(8) UNSIGNED NOT NULL,
  `user` int(8) UNSIGNED NOT NULL,
  `cost` decimal(10,2) UNSIGNED NOT NULL,
  `timestamp` int(11) UNSIGNED NOT NULL,
  `status` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'RECEIVED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ORDERS`
--

INSERT INTO `ORDERS` (`id`, `user`, `cost`, `timestamp`, `status`) VALUES
(32, 1, '33.00', 1649954873, 'PROCESSING'),
(35, 1, '132.00', 1649956951, 'PROCESSING'),
(36, 1, '8.25', 1649957130, 'PROCESSING'),
(37, 1, '8.25', 1649957957, 'PROCESSING'),
(38, 4, '24.00', 1650204175, 'PROCESSING'),
(39, 1, '6.00', 1650207306, 'RECEIVED');

-- --------------------------------------------------------

--
-- Table structure for table `ORDER_ITEMS`
--

CREATE TABLE `ORDER_ITEMS` (
  `orderid` int(8) UNSIGNED NOT NULL,
  `item` int(8) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `ORDER_ITEMS`
--

INSERT INTO `ORDER_ITEMS` (`orderid`, `item`, `quantity`) VALUES
(32, 26, 4),
(35, 26, 16),
(36, 26, 1),
(37, 26, 1),
(38, 27, 4),
(39, 27, 1);

-- --------------------------------------------------------

--
-- Table structure for table `RATINGS`
--

CREATE TABLE `RATINGS` (
  `id` int(8) UNSIGNED NOT NULL,
  `item` int(8) UNSIGNED NOT NULL,
  `user` int(8) UNSIGNED NOT NULL,
  `rating` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `RATINGS`
--

INSERT INTO `RATINGS` (`id`, `item`, `user`, `rating`) VALUES
(1, 26, 1, 4),
(2, 26, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `USERS`
--

CREATE TABLE `USERS` (
  `id` int(8) UNSIGNED NOT NULL,
  `username` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `hash` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `first_name` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `last_name` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `zip` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `admin` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `banned` tinyint(1) UNSIGNED NOT NULL,
  `token` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'N/A',
  `expires` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `USERS`
--

INSERT INTO `USERS` (`id`, `username`, `hash`, `first_name`, `last_name`, `address`, `zip`, `email`, `admin`, `banned`, `token`, `expires`) VALUES
(1, 'admin', '$2y$10$KdAwFH5MvCx5mm19aqoTlOSmPKCcstDefFOTA85nFruUL9Dw3thwO', 'Admin', 'Admin', '123 Street St.', 'A1A 1A1', 'admin@example.com', 1, 0, 'N/A', 0),
(2, 'jay_test', '$2y$10$SqSejW4ZkInpolHUHpEm1e09zxkeRNWLrqNaXFJtF0eaFSmpYv/2i', 'Jay', 'C', '111', '111', 'ttaataf@gmail.com', 0, 0, 'N/A', 0),
(3, 'username', '$2y$10$W30sWd9VTqacaAZXcrXBsO6Fzo5A1fnkLFWrUJF0WiZom.iiLRD6O', 'User', 'Name', '224 Username ave', 'U5E 9M3', 'username@hotmail.com', 0, 0, 'N/A', 0),
(4, 'testuser', '$2y$10$4mLKWcPrzjmWpjVuj6kbCew0.52dKJrb2PhECGNe9oPgMIqOjnO4G', 'test', 'user', 'address', 'zip', 'email', 0, 0, 'N/A', 0),
(5, 'user', '$2y$10$g0wfzKlb4sUy/GS0cO1/FOY339tDOH84zz5lIhGVY8RpKhNK1324y', 'user', 'user', 'usada', 'afds', 'asfaf', 0, 0, 'N/A', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `CART_ITEMS`
--
ALTER TABLE `CART_ITEMS`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item` (`item`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `CATEGORIES`
--
ALTER TABLE `CATEGORIES`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ITEMS`
--
ALTER TABLE `ITEMS`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `ORDERS`
--
ALTER TABLE `ORDERS`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `ORDER_ITEMS`
--
ALTER TABLE `ORDER_ITEMS`
  ADD KEY `orderid` (`orderid`),
  ADD KEY `item` (`item`);

--
-- Indexes for table `RATINGS`
--
ALTER TABLE `RATINGS`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item` (`item`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `USERS`
--
ALTER TABLE `USERS`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `CART_ITEMS`
--
ALTER TABLE `CART_ITEMS`
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `CATEGORIES`
--
ALTER TABLE `CATEGORIES`
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ITEMS`
--
ALTER TABLE `ITEMS`
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `ORDERS`
--
ALTER TABLE `ORDERS`
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `RATINGS`
--
ALTER TABLE `RATINGS`
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `USERS`
--
ALTER TABLE `USERS`
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `CART_ITEMS`
--
ALTER TABLE `CART_ITEMS`
  ADD CONSTRAINT `CART_ITEMS_ibfk_1` FOREIGN KEY (`item`) REFERENCES `ITEMS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `CART_ITEMS_ibfk_2` FOREIGN KEY (`user`) REFERENCES `USERS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ITEMS`
--
ALTER TABLE `ITEMS`
  ADD CONSTRAINT `ITEMS_ibfk_1` FOREIGN KEY (`category`) REFERENCES `CATEGORIES` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `ORDERS`
--
ALTER TABLE `ORDERS`
  ADD CONSTRAINT `ORDERS_ibfk_1` FOREIGN KEY (`user`) REFERENCES `USERS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ORDER_ITEMS`
--
ALTER TABLE `ORDER_ITEMS`
  ADD CONSTRAINT `ORDER_ITEMS_ibfk_1` FOREIGN KEY (`orderid`) REFERENCES `ORDERS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ORDER_ITEMS_ibfk_2` FOREIGN KEY (`item`) REFERENCES `ITEMS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `RATINGS`
--
ALTER TABLE `RATINGS`
  ADD CONSTRAINT `RATINGS_ibfk_1` FOREIGN KEY (`item`) REFERENCES `ITEMS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `RATINGS_ibfk_2` FOREIGN KEY (`user`) REFERENCES `USERS` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
