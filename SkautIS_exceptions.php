<?php
/**
 * Neplatné přihlášení spojené se skautISem
 */
class SkautIS_AuthenticationException extends Exception {}

class SkautIS_Exception extends Exception {}

/**
 * Neplatné wsdl
 */
class SkautIS_WsdlException extends Exception {}

class SkautIS_AbortException extends AbortException {}

class SkautIS_InvalidArgumentException extends InvalidArgumentException {}

/**
 * nepovolený přístup ze strany SkautISu
 */
class SkautIS_PermissionException extends Exception {}


