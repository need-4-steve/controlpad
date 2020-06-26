git filter-branch -f --index-filter 'git rm --cached --ignore-unmatch -r dump/5-mill-sim.sql'
git update-ref -d refs/original/refs/heads/master
git filter-branch -f --index-filter 'git rm --cached --ignore-unmatch -r dump/ce.united.sql'
git update-ref -d refs/original/refs/heads/master
git filter-branch -f --index-filter 'git rm --cached --ignore-unmatch -r dump/games.sql'
git update-ref -d refs/original/refs/heads/master
