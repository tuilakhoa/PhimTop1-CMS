Name:           phimtop1
Version:        1.0.0
Release:        1%{?dist}
Summary:        PhimTop1 Flutter Application

License:        MIT
Source0:        phimtop1-1.0.0.tar.gz

%description
PhimTop1 Flutter Application

%prep
%setup -q -n phimtop1-1.0.0

%install
mkdir -p "%{buildroot}/opt/phimtop1"
cp -a * "%{buildroot}/opt/phimtop1/"

mkdir -p "%{buildroot}/usr/share/applications"
cat <<'DESKTOP' > "%{buildroot}/usr/share/applications/phimtop1.desktop"
[Desktop Entry]
Name=PhimTop1
Exec=/opt/phimtop1/phimtop1_flutter
Icon=/opt/phimtop1/data/flutter_assets/assets/logo.png
Terminal=false
Type=Application
Categories=Entertainment;
DESKTOP

mkdir -p "%{buildroot}/usr/bin"
ln -s /opt/phimtop1/phimtop1_flutter "%{buildroot}/usr/bin/phimtop1"

%files
/opt/phimtop1
/usr/share/applications/phimtop1.desktop
/usr/bin/phimtop1
