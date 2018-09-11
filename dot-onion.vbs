' :: Overview ::
' We need to curl an endpoint with words with curl
' then we run it through scallion
' and post the output back to endpoint
' Repeat as necessary

' :: Dependencies ::
' scallion: https://github.com/lachesis/scallion
' curl: https://blogs.technet.microsoft.com/virtualization/2017/12/19/tar-and-curl-come-to-windows/

' :: Variables ::
pid = "alpha" ' modified with whatever is first on command line
pts = "C:\Users\user\Desktop\scallion-v2.0" ' path to scallion executable

' :: Program ::
' arguments
if WScript.Arguments.Count > 0 then
	pid = WScript.Arguments.Item(0)
end if
Set filesys = CreateObject("Scripting.FileSystemObject") 
Do ' start of fun

' curl an endpoint, look for data
Wscript.Echo(now & "> cURLing for next word")
Set curlShell = WScript.CreateObject("WScript.Shell")
params = chr(34) & "http://192.168.1.221/words.php?get=new" & chr(34)
Set curlExec = curlShell.Exec("c:\windows\system32\curl.exe" & " " & params)
theWord = curlExec.StdOut.ReadAll
if theWord = "" then
	Wscript.Echo(now & "> No more words")
	WScript.Quit
end if
Wscript.Echo(now & "> got the word: " & theWord)

' execute the hash finder with some parameters
' we want the word to end in a number for readability
Wscript.Echo(now & "> Executing Scallion")
' https://stackoverflow.com/questions/11501044/i-need-execute-a-command-line-in-a-visual-basic-script
Set shell = WScript.CreateObject("WScript.Shell")
params = "--keysize=1024 --output=" & pid & ".keys --skip-sha-test --quit-after=20 " & theWord & "[234567]"
Set Exec =  shell.Exec(pts & "\" & "scallion.exe" & " " & params)
theOutput = Exec.StdOut.ReadAll

' post the file of results up to the server
Wscript.Echo(now & "> sending results file to server")
Set curlShell = WScript.CreateObject("WScript.Shell")
params = "-T " & pid & ".keys " & chr(34) & "http://192.168.1.221/words.php?word=" & theWord & chr(34)
Set curlExec = curlShell.Exec("c:\windows\system32\curl.exe" & " " & params)
Wscript.Echo(now & "> Uploaded")

' STUPID CRAPPY ASS ASYNC SOMETHING SLEEP BEFORE DELETING
WScript.Sleep 1000
filesys.DeleteFile pid & ".keys"

Loop Until 0 = 1 ' infinite loop