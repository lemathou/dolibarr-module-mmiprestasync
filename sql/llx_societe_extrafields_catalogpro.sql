-- RM2560 — Extrafields « catalogue pro » sur les tiers.
--
-- Alimentes par le middleware doli-presta-sync a chaque synchro client, d'apres
-- les catalogues auxquels le client a REELLEMENT acces cote PrestaShop (regle de
-- modules/mmi_catalogpro/catalog.php : groupe + all_group + liste clients).
--
-- Script REJOUABLE : a passer AVANT la premiere synchro, sinon l'UPDATE du
-- middleware ne trouve pas les colonnes et echoue en silence.
--
--   mysql <base> < llx_societe_extrafields_catalogpro.sql
--
-- Les ALTER TABLE ne sont pas conditionnels en MySQL : ils sont joues via une
-- procedure qui teste information_schema, pour rester rejouables sans erreur.

DELIMITER //

DROP PROCEDURE IF EXISTS mmi_add_column_if_missing //
CREATE PROCEDURE mmi_add_column_if_missing(
	IN p_table VARCHAR(64), IN p_column VARCHAR(64), IN p_definition VARCHAR(255))
BEGIN
	IF NOT EXISTS (
		SELECT 1 FROM information_schema.COLUMNS
		WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = p_table AND COLUMN_NAME = p_column
	) THEN
		SET @sql = CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN `', p_column, '` ', p_definition);
		PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;
	END IF;
END //

DELIMITER ;

CALL mmi_add_column_if_missing('llx_societe_extrafields', 'p_catalogpro',      'int(1) DEFAULT NULL');
CALL mmi_add_column_if_missing('llx_societe_extrafields', 'p_catalogpro_url',  'varchar(255) DEFAULT NULL');
CALL mmi_add_column_if_missing('llx_societe_extrafields', 'p_catalogpro_info', 'varchar(255) DEFAULT NULL');

DROP PROCEDURE IF EXISTS mmi_add_column_if_missing;

-- Declaration des champs (list=1 : visibles ET filtrables dans la liste des tiers).
-- `param` reprend la valeur serialisee utilisee par les autres extrafields du module.
INSERT INTO `llx_extrafields`
	(`name`, `entity`, `elementtype`, `label`, `type`, `size`, `fieldrequired`, `enabled`,
	 `pos`, `alwayseditable`, `param`, `list`, `printable`, `totalizable`, `datec`)
SELECT * FROM (SELECT
	'p_catalogpro' AS name, 1 AS entity, 'societe' AS elementtype,
	'Catalogue pro' AS label, 'boolean' AS type, '' AS size, 0 AS fieldrequired, '1' AS enabled,
	110 AS pos, 0 AS alwayseditable,
	'a:1:{s:7:"options";a:1:{s:0:"";N;}}' AS param, '1' AS list, 0 AS printable,
	0 AS totalizable, NOW() AS datec) AS t
WHERE NOT EXISTS (SELECT 1 FROM `llx_extrafields`
	WHERE `name` = 'p_catalogpro' AND `elementtype` = 'societe' AND `entity` = 1);

INSERT INTO `llx_extrafields`
	(`name`, `entity`, `elementtype`, `label`, `type`, `size`, `fieldrequired`, `enabled`,
	 `pos`, `alwayseditable`, `param`, `list`, `printable`, `totalizable`, `datec`)
SELECT * FROM (SELECT
	'p_catalogpro_url' AS name, 1 AS entity, 'societe' AS elementtype,
	'Catalogue pro : lien' AS label, 'url' AS type, '255' AS size, 0 AS fieldrequired, '1' AS enabled,
	111 AS pos, 0 AS alwayseditable,
	'a:1:{s:7:"options";a:1:{s:0:"";N;}}' AS param, '1' AS list, 0 AS printable,
	0 AS totalizable, NOW() AS datec) AS t
WHERE NOT EXISTS (SELECT 1 FROM `llx_extrafields`
	WHERE `name` = 'p_catalogpro_url' AND `elementtype` = 'societe' AND `entity` = 1);

INSERT INTO `llx_extrafields`
	(`name`, `entity`, `elementtype`, `label`, `type`, `size`, `fieldrequired`, `enabled`,
	 `pos`, `alwayseditable`, `param`, `list`, `printable`, `totalizable`, `datec`)
SELECT * FROM (SELECT
	'p_catalogpro_info' AS name, 1 AS entity, 'societe' AS elementtype,
	'Catalogue pro : information' AS label, 'varchar' AS type, '255' AS size, 0 AS fieldrequired, '1' AS enabled,
	112 AS pos, 0 AS alwayseditable,
	'a:1:{s:7:"options";a:1:{s:0:"";N;}}' AS param, '1' AS list, 0 AS printable,
	0 AS totalizable, NOW() AS datec) AS t
WHERE NOT EXISTS (SELECT 1 FROM `llx_extrafields`
	WHERE `name` = 'p_catalogpro_info' AND `elementtype` = 'societe' AND `entity` = 1);
