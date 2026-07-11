-- Migration 0001: cria a tabela `predefinicoes`
-- Esta migration só roda uma vez por banco (controlado pela tabela _migrations).
-- Usa "IF NOT EXISTS" como segurança extra, caso já tenha sido criada manualmente.
 
CREATE TABLE IF NOT EXISTS `predefinicoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(45) NOT NULL,
  `descricao` varchar(90) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `categoria` varchar(90) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_alteracao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_predefinicoes_usuarios_idx` (`id_usuario`),
  CONSTRAINT `fk_predefinicoes_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
