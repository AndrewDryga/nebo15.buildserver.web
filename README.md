# Builds API

- [Добавление сборки](#upload)
- [Получение списка последних сборок](#latest)


## Добавление сборки {#upload}

Параметры:

* `app_id` - id для авторизации
* `app_secret` - код для авторизации
* `name` - наименование сборки
* `build` - ID сборки
* `branch` - ветка
* `repository` - репозиторий
* `bundle` - пакет
* `version` - версия
* `comment` - комментарий
* `build_file` - файл сборки

```shell
$ curl -uAPP_ID:APP_SECRET \
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
     -F "build_app_file=@./var/test.app" \
     http://builds.nebo15.dev/upload.json
```


```json
{
    "meta": {
        "code": 200
    },
    "data": {
        "id": "543676aca58ecfa2610041d7",
        "date": "2014-10-09 03:51:08",
        "plist_path": "2014-10-09 03:51:08",
        "name": "Walletz",
        "version": "2.2",
        "build": "2.1.102",
        "slug": "Nebo15\/mbank.ios",
        "travis_build_id": "12412401",
        "travis_build_number": null,
        "travis_job_id": "12412401",
        "travis_job_number": "1",
        "branch": "master",
        "commit": "d112fdd",
        "commit_range": "d112fdd..d112fdd",
        "bundle": "com.nebo15.mbank.develop",
        "server_id": "SERVER_DEV",
        "comment": null,
        "build_plist_url": "itms-services:\/\/?action=download-manifest&url=itms-services:\/\/?action=download-manifest&url=http:\/\/builder.nebo15.dev\/builds\/543676aca58ecfa2610041d7\/Walletz.plist"
    }
}
```

## Получение списка последних сборок {#latest}

Параметры:

* `app_id` - id для авторизации
* `app_secret` - код для авторизации

```shell
$ curl -u APP_ID:APP_SECRET http://builds.nebo15.me/latest.json
```
```json
{
    "meta": {
        "code": 200
    },
    "data": {
        "53e5073e6f88dac11f746eab": {
            "build": "test build",
            "build_filename": "builder",
            "branch": "test branch",
            "repository": "test repository",
            "name": "test nam 71",
            "bundle": "test bundle",
            "version": "test version",
            "comment": "putin huilo",
            "build_plist_url": "itms-services:\/\/?action=download-manifest&url=itms-services:\/\/?action=download-manifest&url=http:\/\/builder.nebo15.me\/builds\/53e5073e6f88dac11f746eab\/test_nam_71.plist"
        },
        "53e5076e6f88dac11f4a4242": {
            "build": "test build",
            "build_filename": "builder32.ipa",
            "branch": "test branch",
            "repository": "test repository",
            "name": "test name 247",
            "bundle": "test bundle",
            "version": "test version",
            "comment": "putin huilo",
            "build_plist_url": "itms-services:\/\/?action=download-manifest&url=itms-services:\/\/?action=download-manifest&url=http:\/\/builder.nebo15.me\/builds\/53e5076e6f88dac11f4a4242\/test_name_247.plist"
        }
    }
}
```
