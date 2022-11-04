# Buto-Plugin-BanksigneringApi

BankID API.
Use plugin banksignering/ui.


## data/data.yml

````
mode: null
url:
  test: 'http://banksign-test.azurewebsites.net/api/'
  prod: 'https://api.banksignering.se/api/'
account:
  apiUser: "xxx"
  password: "yyy"
  companyApiGuid: "zzz"
endpoint:
  auth:
    apiUser: "xxx"
    password: "yyy"
    companyApiGuid: "zzz"
    endUserIp: ""
    personalNumber: ""
    tokenStartRequired: false
  sign:
    apiUser: "xxx"
    password: "yyy"
    companyApiGuid: "zzz"
    endUserIp: ""
    userVisibleData: "Text to sign"
    userNonVisibleData: "Sign with Buto plugin banksignering/api."
  collectstatus:
    apiUser: "xxx"
    password: "yyy"
    companyApiGuid: "zzz"
    orderRef: ""
  collectqr:
    apiUser: "xxx"
    password: "yyy"
    companyApiGuid: "zzz"
    orderRef: ""
````

### endpoint/auth/personalNumber
If this is set BankID app will be launched direkt. But this will be depricated in maj 2024.