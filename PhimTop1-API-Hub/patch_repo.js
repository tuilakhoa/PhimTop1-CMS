const fs = require('fs');

const repoPath = '/home/khoa/Bản tải về/PhimTop1-CMS/includes/repo/MovieRepository.php';
let content = fs.readFileSync(repoPath, 'utf8');

const targetStr = "updated_at=VALUES(updated_at)";
const newStr = "updated_at=IF(episode_current != VALUES(episode_current) OR quality != VALUES(quality) OR status != VALUES(status) OR year != VALUES(year), VALUES(updated_at), updated_at)";

content = content.replace(targetStr, newStr);

fs.writeFileSync(repoPath, content, 'utf8');
console.log("Patched MovieRepository.php");
