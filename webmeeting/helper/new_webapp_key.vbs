' Name: 
'     NEW_WEBAPP_KEY
' Description:
'     This script demos how to automate the creation public/private keys for web application
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
sIdentity = "key_webapp_bar"

' Password used for encryting the private key
sPassphrase = "my_webapp_passphrase"

' Location of MCU's configuration file
sMcuConfigPath = "..\mcu\configm.ini"

' PHP file that contains the definition of passphrase and key setting
sWebConfigPath = "..\web\include\hit_settings.php"

' Folder where MCU's public key will be placed
sWebAppKeyFolder = "..\key\"

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
FSO.CopyFile sPublicKeyPath, sMcuKeyFolder & sPublicKeyPath, OVERWRITE_EXISTING_FILE
FSO.CopyFile sPrivateKeyPath, sWebAppKeyFolder & sPrivateKeyPath, OVERWRITE_EXISTING_FILE


'--- Clean up files for security
Cleanup






'-------------------------------------------------------
' Update MCU's settings
'-------------------------------------------------------
' There are no settings to make for MCU.





'-------------------------------------------------------
' Update Web Application's settings
'-------------------------------------------------------
'--- Read in the Web Application JNJ setting file
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

' Private Key Path
REGEXP.Pattern = "([\n\r][ \t]*define\([ \t]*""DEFAULT_PRIVATE_KEY""[ \t]*,[ \t]*)[^\n\r]*"
Set oMatches = REGEXP.Execute( sContent )
If oMatches.Count = 0 Then
	Panic "Unable to find DEFAULT_PRIVATE_KEY entry in Web configuration file"
End If
sContent = REGEXP.Replace( sContent, "$1" & """" & sIdentity & """);" )

' Site ID
REGEXP.Pattern = "([\n\r][ \t]*define\([ \t]*""DEFAULT_SITE_ID""[ \t]*,[ \t]*)[^\n\r]*"
Set oMatches = REGEXP.Execute( sContent )
If oMatches.Count = 0 Then
	Panic "Unable to find DEFAULT_SITE_ID entry in Web configuration file"
End If
sContent = REGEXP.Replace( sContent, "$1" & """" & sIdentity & """);" )

' Passphrase
REGEXP.Pattern = "([\n\r][ \t]*define\([ \t]*""DEFAULT_PASS_PHRASE""[ \t]*,[ \t]*)[^\n\r]*"
Set oMatches = REGEXP.Execute( sContent )
If oMatches.Count = 0 Then
	Panic "Unable to find DEFAULT_PASS_PHRASE entry in Web configuration file"
End If
sContent = REGEXP.Replace( sContent, "$1" & "base64_decode( """ & sBase64Passphrase & """));" )


'--- Save configuration changes
On Error Resume Next
	Set hFile = FSO.OpenTextFile( sWebConfigFullPath, FOR_WRITING, ASCII_MODE )
	hFile.Write( sContent )
	hFile.Close()
	If Err <> 0 Then
		Panic "Unable to write web configuration setting file [" & sWebConfigFullPath & "]. " 
	End If		
On Error Goto 0












MsgBox "Successfully create Web Application key and update associated configuration." , vbInformation + vbOKOnly





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