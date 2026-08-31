[Setup]
AppName=PhimTop1
AppVersion=1.0.0
DefaultDirName={autopf}\PhimTop1
DefaultGroupName=PhimTop1
OutputDir=Output
OutputBaseFilename=PhimTop1_Setup
Compression=lzma
SolidCompression=yes
SetupIconFile=runner\resources\app_icon.ico
UninstallDisplayIcon={app}\phimtop1_flutter.exe

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked

[Files]
Source: "..\build\windows\x64\runner\Release\phimtop1_flutter.exe"; DestDir: "{app}"; Flags: ignoreversion
Source: "..\build\windows\x64\runner\Release\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{group}\PhimTop1"; Filename: "{app}\phimtop1_flutter.exe"
Name: "{autodesktop}\PhimTop1"; Filename: "{app}\phimtop1_flutter.exe"; Tasks: desktopicon

[Run]
Filename: "{app}\phimtop1_flutter.exe"; Description: "{cm:LaunchProgram,PhimTop1}"; Flags: nowait postinstall skipifsilent
