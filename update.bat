@echo off
echo 請先將本次更新的檔案放到d:\elearn.tmp 目錄下
echo 若尚未放置，請按Ctrl+C 中斷，若已放置則10秒後繼續操作。
echo ....
echo .......
choice /C YN /N /T 10 /D y > nul
echo 即將把 d:\elearn.tmp 目錄下的檔案複製到 d:\elearn.git 目錄進行覆蓋更新。

xcopy d:\elearn.tmp\* d:\elearn.git\zenlin2 /s
echo 請先確認目前的 git tag 版本
git add *
set /p var1=請輸入旭聯版控版本次:
echo 所輸入的版本次為 %var1%
git push -u origin main
set /p var2=請輸入本次要標示的git版本:
echo 所輸入的陸令更新版本次為%var2%
cd d:\elearn.git\zenlin2
git tag -am "%var1%"  %var2%
git push origin --tag


pause