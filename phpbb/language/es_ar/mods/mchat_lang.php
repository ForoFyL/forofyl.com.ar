<?php
/**
*
* @package - mChat
* @version 1.3.5 07.10.2009
* @copyright (c) djs596 ( http://djs596.com/ ), (c) RMcGirr83 ( http://www.rmcgirr83.org/ ), (c) Stokerpiller ( http://www.phpbb3bbcodes.com/ )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

/**
* DO NOT CHANGE!
*/
if (!defined('IN_PHPBB'))
{
  exit;
}

if (empty($lang) || !is_array($lang))
{
  $lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(

	// MCHAT
	'MCHAT_ADD'					=> 'Enviar',
	'MCHAT_ARCHIVE'				=> 'Archivo',	
	'MCHAT_ARCHIVE_PAGE'		=> 'Archivo del Mini-Chat',
	'MCHAT_ARCHIVENOMESSAGE'	=> 'No hay mensajes aquí.',	
	'MCHAT_AUTOUPDATE'			=> 'Autoactualización cada <strong>%d</strong> segundos',
	'MCHAT_BBCODES'				=> 'BBCodes',
	'MCHAT_CLEAN'				=> 'X',
	'MCHAT_CLEANED'				=> 'Todos los mensajes han sido exitosamente removidos.',
	'MCHAT_COPYRIGHT_CHECK'		=> '&copy; <a href="http://www.phpbb3bbcodes.com/">phpBB3BBCodes.com</a>',
	'MCHAT_DELALLMESS'			=> 'Remover todos los mensajes?',
	'MCHAT_DELCONFIRM'			=> 'Confirmás el removimiento?',
	'MCHAT_DELITE'				=> 'Borrar',
	'MCHAT_EDIT'				=> 'Editar',
	'MCHAT_EDITINFO'			=> 'Editá el mensaje y clickeá OK',
	'MCHAT_ENABLE'				=> 'Disculpas, el Mini-Chat no está disponible en este momento',	
	'MCHAT_ERROR'				=> 'Error',	
	'MCHAT_FLOOD'				=> 'No podés enviar otro mensaje tan próximo al anterior',	
	'MCHAT_HELP'				=> '?',
	'MCHAT_HELP_INFO'			=> 'Reglas: \n1. No insultar\n2. No publicites tu propio sitio\n3. No dejes\n4. Don’t leave a pointless message\n5. Don’t leave a message consisting of only smilies',	// \n signifies a new line //
	'MCHAT_IP'					=> 'IP:',
	'MCHAT_IP_WHOIS_FOR'		=> 'IP whois for %s',	
	'MCHAT_LOAD'				=> 'Actualizando...',
	'MCHAT_NO_CUSTOM_PAGE'		=> 'La página de chat no está disponible en este momento',	
	'MCHAT_NOACCESS'			=> 'No tenés permisos para postear en el mChat',
	'MCHAT_NOACCESS_ARCHIVE'	=> 'No tenés permisos para ver el archivo',	
	'MCHAT_NOJAVASCRIPT'		=> 'Tu navegador no soporta JavaScript o JavaScript está desactivado',		
	'MCHAT_NOMESSAGE'			=> 'Sin mensajes',
	'MCHAT_NOMESSAGEINPUT'		=> 'No ingresaste un mensaje',
	'MCHAT_NOSMILE'				=> 'No se encontraron emoticones',
	'MCHAT_OK'					=> 'OK',
	'MCHAT_PERMISSIONS'			=> 'Cambiar los permisos de usuario',
	'MCHAT_REPLACE_COPYRIGHT'	=> 'You have removed the copyright from the mChat mod.<br />Please restore the entry for the copyright by editing language/en/mods/mchat_lang.php.<br />Until you do so, both this mod and your forum will not work unless you uninstall mChat.',
	'MCHAT_SMILES'				=> 'Emoticones',
	'MCHAT_TITLE'				=> 'Mini-Chat',

	'MCHAT_TOTALMESSAGES'		=> 'Mensajes totales: <strong>%s</strong>',
	'MCHAT_USESOUND'			=> 'Activar sonido?',
	
	// whois chatting stuff
	'MCHAT_GUEST_USERS_TOTAL'			=> '%d invitados',
	'MCHAT_GUEST_USERS_ZERO_TOTAL'		=> '0 invitados',
	'MCHAT_GUEST_USER_TOTAL'			=> '%d invitado',	
	'MCHAT_HIDDEN_USERS_TOTAL'			=> '%d oculto',
	'MCHAT_HIDDEN_USERS_TOTAL_AND'		=> '%d oculto y ',
	'MCHAT_HIDDEN_USERS_ZERO_TOTAL'		=> '0 oculto',
	'MCHAT_HIDDEN_USERS_ZERO_TOTAL_AND'	=> '0 oculto y ',
	'MCHAT_HIDDEN_USER_TOTAL'			=> '%d oculto',
	'MCHAT_HIDDEN_USER_TOTAL_AND'		=> '%d oculto y ',
	'MCHAT_ONLINE_USERS_TOTAL'			=> 'En total hay <strong>%d</strong> usuarios chateando. :: ',
	'MCHAT_ONLINE_USERS_ZERO_TOTAL'		=> 'En total hay <strong>0</strong> usuarios chateando. :: ',
	'MCHAT_ONLINE_USER_TOTAL'			=> 'En total hay <strong>%d</strong> usuarios chateando. :: ',	
	'MCHAT_REG_USERS_TOTAL'				=> '%d registrados, ',
	'MCHAT_REG_USERS_TOTAL_AND'			=> '%d registrados y ',
	'MCHAT_REG_USERS_ZERO_TOTAL'		=> '0 registrados, ',
	'MCHAT_REG_USERS_ZERO_TOTAL_AND'	=> '0 registrados y ',
	'MCHAT_REG_USER_TOTAL'				=> '%d registrados, ',
	'MCHAT_REG_USER_TOTAL_AND'			=> '%d registrados y ',	

	
	'WHO_IS_CHATTING'			=> 'Quiçen estça chateando',
	'WHO_IS_REFRESH_EXPLAIN'	=> 'Actualiza cada <strong>%d</strong> segundos',
	'WHO_IS_REFRESHING'			=> 'Actualizando',
		
	
	// BBCode Font
	'MCHAT_FONTSIZE'	=> 'Tamaño de la fuente:',
	'MCHAT_FONTTINY'	=> 'Diminuto',
	'MCHAT_FONTSMALL'	=> 'Pequeño',
	'MCHAT_FONTNORMAL'	=> 'Normal',
	'MCHAT_FONTLARGE'	=> 'Grande',
	'MCHAT_FONTHUGE'	=> 'Enorme',

));
?>