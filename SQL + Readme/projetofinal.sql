-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3310
-- Tempo de geração: 12-Ago-2025 às 14:08
-- Versão do servidor: 5.7.24
-- versão do PHP: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `projetofinal`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estrutura da tabela `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) DEFAULT NULL,
  `descricao` text,
  `data` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `local` varchar(150) DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `events`
--

INSERT INTO `events` (`id`, `titulo`, `descricao`, `data`, `hora`, `local`, `preco`, `imagem`, `stock`) VALUES
(1, 'Concerto Linkin Park', 'Concerto da banda Linkin Park ao vivo com todos os clássicos.', '2025-10-29', '21:00:00', 'Altice Arena, Lisboa', '45.00', 'images/banda1.jpg', 18989),
(2, 'Festival de Verão', 'Festival com várias bandas de rock, pop e música eletrónica.', '2025-08-23', '16:00:00', 'Parque da Cidade, Porto', '35.00', 'images/festivalverao.jpg', 8000),
(3, 'Stand Up Comedy Night', 'Noite de comédia com alguns dos melhores comediantes portugueses.', '2025-10-20', '20:30:00', 'Teatro Tivoli, Lisboa', '20.00', 'images/standup.jpg', 1000),
(4, 'Feira de Tecnologia', 'Exposição de novas tecnologias, startups e palestras.', '2025-09-10', '10:00:00', 'Centro de Congressos, Lisboa', '15.00', 'images/evento_6890d716313276.98674706.jpg', 9000),
(5, 'Concertos de Verão', 'Varias bandas a atuar e cantores ao vivo.', '2025-09-28', '22:00:00', 'Estádio Municipal de Braga', '30.00', 'images/veraobraga.jpg', 30000),
(6, 'Coldplay - Music of the Spheres Tour', 'Concerto da banda Coldplay no estádio do Dragão', '2025-10-05', '21:00:00', 'Estádio do Dragão, Porto', '40.00', 'images/estadiodragao.jpg', 50000),
(7, 'Festival Indie Porto 2025', 'Festival Indie no Porto', '2025-10-19', '17:00:00', 'Hard Club, Porto', '25.00', 'images/hard-club-porto.jpg', 400),
(8, 'Teatro: O Auto da Barca do Inferno', 'Peça de teatro : O auto da barca do Inferno', '2025-11-12', '19:30:00', 'Teatro Nacional D. Maria II, Lisboa', '15.00', 'images/Teatro-Nacional-D-Maria-II-Lisboa.jpg', 900),
(9, 'Ricardo Araújo Pereira - Stand Up', 'Stand up do humorista Ricardo Araújo Pereira em Lisboa', '2025-10-16', '21:00:00', 'Coliseu dos Recreios, Lisboa', '20.00', 'images/Coliseu dos recreios.jpg', 4000);

-- --------------------------------------------------------

--
-- Estrutura da tabela `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `data_compra` datetime DEFAULT NULL,
  `estado` enum('Pendente','Confirmado','Cancelado') DEFAULT 'Pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `purchases`
--

INSERT INTO `purchases` (`id`, `user_id`, `event_id`, `quantidade`, `data_compra`, `estado`) VALUES
(1, 2, 1, 3, '2025-06-18 14:49:16', 'Confirmado'),
(6, 1, 1, 1, '2025-07-21 14:41:08', 'Confirmado');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'Admin1', 'admin123@gmail.com', '$2y$10$58obxg96EsciMkeZ1IF.dO17Re2ST7hSKz5imMDLCZYTeCAUi1PT6', 'admin'),
(2, 'user2', 'ise23r@gmail.com', '$2y$10$upZ9Jv4R6bypIdnnhKVF7.JNBV/n6mRkTuZDulcbFAeAiv6CkV5Y.', 'user');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Limitadores para a tabela `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
