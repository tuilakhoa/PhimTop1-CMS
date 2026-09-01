#!/bin/bash
set -e
cd /home/khoa/Bản\ tải\ về/phimtop1cms/phimtop1_flutter

echo "Creating tarball for RPM..."
rm -rf rpmbuild/SOURCES/phimtop1-1.0.0
mkdir -p rpmbuild/SOURCES/phimtop1-1.0.0
cp -r build/linux/x64/release/bundle/* rpmbuild/SOURCES/phimtop1-1.0.0/

cd rpmbuild/SOURCES
tar -czvf phimtop1-1.0.0.tar.gz phimtop1-1.0.0
cd ../..

echo "Building RPM..."
rpmbuild --define "_topdir $(pwd)/rpmbuild" -ba rpmbuild/SPECS/phimtop1.spec
echo "RPM build finished. Files are in rpmbuild/RPMS"
