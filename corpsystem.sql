# MySQL-Front 5.1  (Build 3.35)

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;
/*!40101 SET SQL_MODE='' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;
/*!40103 SET SQL_NOTES='ON' */;


# Host: localhost    Database: corpsystem
# ------------------------------------------------------
# Server version 5.5.16

USE `corpsystem`;

#
# Source for table cliente
#

CREATE TABLE `cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `cliente` varchar(80) DEFAULT NULL,
  `documento` varchar(18) DEFAULT NULL,
  `nascimento` date DEFAULT NULL,
  `telefone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `cep` varchar(9) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(255) DEFAULT NULL,
  `complemento` varchar(255) DEFAULT NULL,
  `bairro` varchar(255) DEFAULT NULL,
  `cidade` varchar(255) DEFAULT NULL,
  `estado` varchar(255) DEFAULT NULL,
  `observacao` text,
  `origem` varchar(255) DEFAULT NULL,
  `status` enum('Ativo','Inativo','Removido') DEFAULT 'Ativo',
  `newsletter` enum('Sim','Não') DEFAULT 'Sim',
  `data` datetime DEFAULT NULL,
  `modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `indice1` (`cliente`,`documento`),
  KEY `indice2` (`origem`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Dumping data for table cliente
#


#
# Source for table estoque
#

CREATE TABLE `estoque` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` char(7) DEFAULT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `modelo` varchar(120) DEFAULT NULL,
  `categoria` varchar(120) DEFAULT NULL,
  `descricao` text,
  `quantidade` int(11) DEFAULT NULL,
  `bruto` float DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `status` enum('Ativo','Inativo','Removido') DEFAULT 'Ativo',
  `data` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Dumping data for table estoque
#


#
# Source for table logs
#

CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `requisicao` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `agente` varchar(255) DEFAULT NULL,
  `tempo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

#
# Dumping data for table logs
#


#
# Source for table pedidos
#

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `loja_id` int(11) DEFAULT NULL,
  `codigo` char(32) DEFAULT NULL,
  `status` enum('Pendente','Cancelado','Produção','Enviado','Concluído','Removido') DEFAULT 'Pendente',
  `postagem` enum('Sedex','Sedex10','SedexHoje','PAC','Carta Registrada','Retira','Entrega') DEFAULT NULL,
  `rastreio` varchar(255) DEFAULT NULL,
  `conferido` enum('Sim','Não') DEFAULT 'Sim',
  `origem` enum('Online','Loja') DEFAULT 'Online',
  `data` date DEFAULT NULL,
  `data_status` datetime DEFAULT NULL,
  `modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `indice1` (`cliente_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Dumping data for table pedidos
#


#
# Source for table pedidos_itens
#

CREATE TABLE `pedidos_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `descricao` text,
  `quantidade` int(11) DEFAULT '1',
  `custo` float DEFAULT NULL,
  `valor` float DEFAULT NULL,
  `tipo` enum('Produto','Encargo','Postagem') DEFAULT 'Produto',
  `status` enum('Ativo','Cancelado') DEFAULT 'Ativo',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Dumping data for table pedidos_itens
#


#
# Source for table usuario
#

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT '0',
  `nome` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `usuario` varchar(255) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `nivel` enum('Administrador','Gerente','Colaborador') DEFAULT 'Colaborador',
  `status` enum('Ativo','Inativo','Removido') DEFAULT 'Ativo',
  `data` datetime DEFAULT NULL,
  `modificado` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Dumping data for table usuario
#

INSERT INTO `usuario` VALUES (1,0,'Marcelo B.','marcelo@analisedesistemas.net','corpsystem','123456','Administrador','Ativo','2022-11-05 11:59:00','2022-11-05 11:54:15');

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
