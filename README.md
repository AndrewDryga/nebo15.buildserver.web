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
$ curl  -F build_file=@/build/file/path -uAPP_ID:APP_SECRET
 -d '{"name": "My build", "build": "111", "branch": "dev", "repository": "github.com/my-repo",
 "bundle": "iOS", "version": "1.1", "comment": "1.1" }'
 http://builds.nebo15.me/upload
```


```json
{
    "meta": {
        "code": 200
    },
    "data": {
        "success": true,
        "code": 200
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