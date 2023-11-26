-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25-Nov-2023 às 20:31
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `cgae_3`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `aluno`
--

CREATE TABLE `aluno` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `sexo` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `quarto` int(11) NOT NULL,
  `cama` smallint(6) NOT NULL,
  `serie` int(11) NOT NULL,
  `id_refeitorio` int(11) NOT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `pernoite` tinyint(1) NOT NULL DEFAULT 0,
  `cidade` varchar(255) NOT NULL,
  `responsavel` varchar(255) NOT NULL,
  `telefone_responsavel` varchar(100) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `assistente`
--

CREATE TABLE `assistente` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pernoite`
--

CREATE TABLE `pernoite` (
  `id` int(11) NOT NULL,
  `aluno` int(11) NOT NULL,
  `pai` int(11) DEFAULT NULL,
  `ativa` tinyint(1) NOT NULL DEFAULT 1,
  `endereco` text NOT NULL,
  `nome_responsavel` varchar(255) NOT NULL,
  `telefone` varchar(50) NOT NULL,
  `data_saida` date NOT NULL,
  `data_chegada` date NOT NULL,
  `hora_saida` time NOT NULL,
  `hora_chegada` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `saida`
--

CREATE TABLE `saida` (
  `id` int(11) NOT NULL,
  `aluno` int(11) NOT NULL,
  `pai` int(11) DEFAULT NULL,
  `ativa` tinyint(1) NOT NULL DEFAULT 1,
  `destino` text NOT NULL,
  `data_saida` date NOT NULL,
  `data_chegada` date NOT NULL,
  `hora_saida` time NOT NULL,
  `hora_chegada` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `vai_volta`
--

CREATE TABLE `vai_volta` (
  `id` int(11) NOT NULL,
  `aluno` int(11) NOT NULL,
  `pai` int(11) DEFAULT NULL,
  `ativa` tinyint(1) NOT NULL DEFAULT 1,
  `destino` text NOT NULL,
  `data` date NOT NULL,
  `hora_saida` time NOT NULL,
  `hora_chegada` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `aluno`
--
ALTER TABLE `aluno`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `assistente`
--
ALTER TABLE `assistente`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `pernoite`
--
ALTER TABLE `pernoite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aluno` (`aluno`),
  ADD KEY `pai` (`pai`);

--
-- Índices para tabela `saida`
--
ALTER TABLE `saida`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aluno` (`aluno`),
  ADD KEY `pai` (`pai`);

--
-- Índices para tabela `vai_volta`
--
ALTER TABLE `vai_volta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aluno` (`aluno`),
  ADD KEY `pai` (`pai`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aluno`
--
ALTER TABLE `aluno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `assistente`
--
ALTER TABLE `assistente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pernoite`
--
ALTER TABLE `pernoite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `saida`
--
ALTER TABLE `saida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `vai_volta`
--
ALTER TABLE `vai_volta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `pernoite`
--
ALTER TABLE `pernoite`
  ADD CONSTRAINT `pernoite_ibfk_1` FOREIGN KEY (`aluno`) REFERENCES `aluno` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pernoite_ibfk_2` FOREIGN KEY (`pai`) REFERENCES `pernoite` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `saida`
--
ALTER TABLE `saida`
  ADD CONSTRAINT `saida_ibfk_1` FOREIGN KEY (`aluno`) REFERENCES `aluno` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `saida_ibfk_2` FOREIGN KEY (`pai`) REFERENCES `saida` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `vai_volta`
--
ALTER TABLE `vai_volta`
  ADD CONSTRAINT `vai_volta_ibfk_1` FOREIGN KEY (`aluno`) REFERENCES `aluno` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vai_volta_ibfk_2` FOREIGN KEY (`pai`) REFERENCES `vai_volta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
