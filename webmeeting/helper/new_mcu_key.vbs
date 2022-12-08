' Name: 
'     NEW_MCU_KEY
' Description:
'     This script demos how to automate the creation of public/private keys for MCU
'     and how to make the associated settings.
' Instruction:
'     Modify variables sIdentity and sPassphrase


'--- Constants
FOR_READING = 1
FOR_WRITING = 2
ASCII_MODE = 0
IGNORE_READONLY_ATTRIB = TRUE
OVERWRITE_EXISTING_FILE = TRUE
Set FSO  = CreateObject("Scripting.FileSystemObject")
Set REGEXP = CreateObject("VBScript.RegExp")



'--- Variables (Change the values below as needed)
' Name for the public/private key
' DO NOT USE "mcu_hostkey". it is reserved for other purposes
sIdentity = "key_mcu_foo"

' Password used for encryting the private key
sPassphrase = "my_mcu_passphrase"

' Location of MCU's configuration file
sMcuConfigPath = "..\mcu\configm.ini"

' PHP file that contains the definition of passphrase and key setting
sWebConfigPath = "..\web\include\hit_settings.php"

' Folder where MCU's public key will be placed
sWebappKeyFolder = "..\key\"

' Folder where MCU's private key will be placed
sMcuKeyFolder = "..\mcu\"

sMcuConfigFullPath = FSO.GetAbsolutePathName( sMcuConfigPath )
sWebConfigFullPath = FSO.GetAbsolutePathName( sWebConfigPath )
sPassPhrasePath = sIdentity & ".passphrase.txt"
sPrivateKeyPath = sIdentity
sPublicKeyPath = sIdentity & ".x509"



'--- Load JNJ Encryptor module
On Error Resume Next
	Set ENCRYPTOR = CreateObject("Hmtg.JnjEncryptor")
	If Err <> 0 Then
		Panic "Unable to load DLL: JnjEncryptor." & vbNewLine & _
		      "Please use REGSVR32 to register HMTG.DLL module."
	End If
On Error Goto 0


'--- Generate private key and public key
ENCRYPTOR.GenKeyPair sIdentity, sPassphrase



'--- Get Base64 encoding of passphrase
On Error Resume Next
	Set hFile = FSO.OpenTextFile( sPassPhrasePath, FOR_READING, ASCII_MODE )
	sBase64Passphrase = hFile.ReadLine()
	hFile.Close()
	If Err <> 0 Then
		Panic "Unable to read passphrase file [" & sPassPhrasePath & "]. " 
	End If		
On Error Goto 0


'--- Move the keys to web application and MCU folders
FSO.CopyFile sPublicKeyPath, sWebappKeyFolder & sPublicKeyPath, OVERWRITE_EXISTING_FILE
FSO.CopyFile sPrivateKeyPath, sMcuKeyFolder & sPrivateKeyPath, OVERWRITE_EXISTING_FILE


'--- Clean up files for security
Cleanup






'-------------------------------------------------------
' Update MCU's settings
'-------------------------------------------------------
'--- Read in the entire MCU configuration file
On Error Resume Next
	Set hFile = FSO.OpenTextFile( sMcuConfigFullPath, FOR_READING, ASCII_MODE )
	sContent = hFile.ReadAll()
	hFile.Close()
	If Err <> 0 Then
		If FSO.FileExists( sMcuConfigFullPath ) Then
			Panic "Unable to read MCU Configuration file [" & sMcuConfigFullPath & "]. " 
		Else
			Panic "MCU Configuration file does not exist [" & sMcuConfigPath & "]"
		End If
	End If		
On Error Goto 0


'--- Update the configuration
REGEXP.Global = True
REGEXP.IgnoreCase = True

' Note: For the regular expression below, 
'  We use [ \t] instead of \s for spaces because \s includes \n in VBScript.
'  Also, we use [^\n\r]* instead of .* because . includes \r
REGEXP.Pattern = "([\n\r][ \t]*mcu_cluster_dh_key[ \t]*=)[^\n\r]*"
Set oMatches = REGEXP.Execute( sContent )
If oMatches.Count = 0 Then
	Panic "Unable to find mcu_cluster_dh_key entry in configm file"
End If
sContent = REGEXP.Replace( sContent, "$1" & sIdentity )

REGEXP.Pattern = "([\n\r][ \t]*passphrase[ \t]*=)[^\n\r]*"
Set oMatches = REGEXP.Execute( sContent )
If oMatches.Count = 0 Then
	Panic "Unable to find passphrase entry in configm file"
End If
sContent = REGEXP.Replace( sContent, "$1" & sBase64Passphrase )



'--- Save configuration changes
On Error Resume Next
	Set hFile = FSO.OpenTextFile( sMcuConfigFullPath, FOR_WRITING, ASCII_MODE )
	hFile.Write( sContent )
	hFile.Close()
	If Err <> 0 Then
		Panic "Unable to write MCU Configuration file [" & sMcuConfigFullPath & "]. " 
	End If		
On Error Goto 0










'-------------------------------------------------------
' Update Web Application's settings
'-------------------------------------------------------
'--- Read in the Web Application configuration file
On Error Resume Next
	Set hFile = FSO.OpenTextFile( sWebConfigFullPath, FOR_READING, ASCII_MODE )
	sContent = hFile.ReadAll()
	hFile.Close()
	If Err <> 0 Then
		If FSO.FileExists( sWebConfigFullPath ) Then
			Panic "Unable to read web configuration file [" & sWebConfigFullPath & "]. " 
		Else
			Panic "Web configuration file does not exist [" & sWebConfigPath & "]"
		End If
	End If		
On Error Goto 0

'--- Update the configuration
REGEXP.Global = True
REGEXP.IgnoreCase = True

REGEXP.Pattern = "([\n\r][ \t]*define\([ \t]*""DEFAULT_PUBLIC_KEY""[ \t]*,[ \t]*)[^\n\r]*"
Set oMatches = REGEXP.Execute( sContent )
If oMatches.Count = 0 Then
	Panic "Unable to find DEFAULT_PUBLIC_KEY entry in the Web configuration file [" & sWebConfigFullPath & "]. "
End If
sContent = REGEXP.Replace( sContent, "$1" & """" & sIdentity & ".x509"");" )



'--- Save configuration changes
On Error Resume Next
	Set hFile = FSO.OpenTextFile( sWebConfigFullPath, FOR_WRITING, ASCII_MODE )
	hFile.Write( sContent )
	hFile.Close()
	If Err <> 0 Then
		Panic "Cannot write to web configuration file [" & sWebConfigFullPath & "]. " 
	End If		
On Error Goto 0












MsgBox "Successfully create MCU key and update associated configuration." , vbInformation + vbOKOnly





Sub Panic( ErrorMessage )
	Cleanup
	MsgBox ErrorMessage, vbCritical + vbOKOnly
	Wscript.Quit(1)
End Sub


Sub Cleanup()
	On Error Resume Next
	FSO.DeleteFile sPassPhrasePath
	FSO.DeleteFile sPublicKeyPath, IGNORE_READONLY_ATTRIB
	FSO.DeleteFile sPrivateKeyPath, IGNORE_READONLY_ATTRIB
	On Error Goto 0
End Sub