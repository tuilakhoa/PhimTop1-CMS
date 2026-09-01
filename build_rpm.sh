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
QA_RPATHS=$(( 0x0001|0x0002 )) rpmbuild --define "_topdir $(pwd)/rpmbuild" -ba rpmbuild/SPECS/phimtop1.spec

echo "Moving RPM out and cleaning up..."
# Find the built RPM and move it to phimtop1_flutter/
find rpmbuild/RPMS -name "*.rpm" -exec mv {} ./phimtop1-linux.rpm \;

# Clean up garbage files
rm -rf rpmbuild/BUILD rpmbuild/BUILDROOT rpmbuild/RPMS rpmbuild/SOURCES rpmbuild/SRPMS

echo "RPM build finished. File is phimtop1-linux.rpm"
