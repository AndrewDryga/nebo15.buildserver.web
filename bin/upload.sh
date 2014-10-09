BNO="102"
BID="12412401"

curl -urandomapp:ThisIsSecretKey \
     -F "name=Walletz" \
     -F "version=2.2" \
     -F "build=2.1.$BNO" \
     -F "slug=Nebo15/mbank.ios" \
     -F "travis_build_id=$BID" \
     -F "travis_job_id=$BID" \
     -F "travis_job_number=1" \
     -F "branch=master" \
     -F "commit=d112fdd" \
     -F "commit_range=d112fdd..d112fdd" \
     -F "bundle=com.nebo15.mbank.develop" \
     -F "server_id=SERVER_DEV" \
     -F "build_file=@./var/test.ipa" \
     http://builds.nebo15.dev/upload.json
