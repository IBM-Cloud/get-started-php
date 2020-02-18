# Getting started with PHP on IBM Cloud

This guide will take you through the steps to get started with a simple PHP application in IBM Cloud and help you:
- Set up a development environment
- Download sample code
- Run the application locally
- Run the application on IBM Cloud Cloud Foundry
- Add a IBM Cloud Database service
- Connect to the database from your local application

## Prerequisites

You'll need the following:
* [IBM Cloud account](https://console.ng.bluemix.net/registration/)
* [IBM Cloud CLI](https://cloud.ibm.com/docs/cli/reference/ibmcloud/download_cli.html)
* [Git](https://git-scm.com/downloads)
* [PHP](http://php.net/downloads.php)
* [Composer](https://getcomposer.org/download/)

## 1. Clone the sample app

Now you're ready to start working with the app. Clone the repo and change the directory to where the sample app is located.
  ```
git clone https://github.com/IBM-Cloud/get-started-php
cd get-started-php
  ```

## 2. Run the app locally

Install dependencies
```
composer install
```

Run the app
  ```
php -S localhost:8000
  ```

View your app at: http://localhost:8000

## 3. Prepare the app for deployment

To deploy to IBM Cloud, it can be helpful to set up a manifest.yml file. One is provided for you with the sample. Take a moment to look at it.

The manifest.yml includes basic information about your app, such as the name, how much memory to allocate for each instance and the route. In this manifest.yml **random-route: true** generates a random route for your app to prevent your route from colliding with others.  You can replace **random-route: true** with **host: myChosenHostName** (without the domain name), supplying a host name of your choice.
 ```
 applications:
 - name: GetStartedPHP
   random-route: true
   memory: 128M
 ```

## 4. Deploy the app

You can use the IBM Cloud CLI to deploy apps.

Log in to your IBM Cloud account, and select an API endpoint.

   ```
ibmcloud login
   ```

If you have a federated user ID, instead use the following command to log in with your single sign-on ID. See [Logging in with a federated ID](https://cloud.ibm.com/docs/iam?topic=iam-federated_id) to learn more.
  ```
ibmcloud login --sso
  ```

Target a Cloud Foundry org and space:

  ```	  
ibmcloud target --cf
  ```

If you don't have an org or a space set up, see [Adding orgs and spaces](https://cloud.ibm.com/docs/account/orgs_spaces.html).

From within the *get-started-php* directory push your app to IBM Cloud

   ```
ibmcloud cf push
   ```

This can take a minute. If there is an error in the deployment process you can use the command `ibmcloud cf logs <Your-App-Name> --recent` to troubleshoot.

When deployment completes you should a message indicating that your app is running.  View your app at the URL listed in the output of the push command. You can also issue the following command to view your apps status and see the URL.

  ```
ibmcloud cf apps
  ```

You can also go to the IBM Cloud [Resource List](https://cloud.ibm.com/resources) to view your app.


## 5. Add a database

Next, we'll add a NoSQL database to this application and set up the application so that it can run locally and on IBM Cloud.

1. Log in to IBM Cloud in your Browser. Browse to the `Dashboard`. Select your application by clicking on its name in the `Name` column.
2. Click on `Connections` then `Connect new`.
3. In the `Data & Analytics` section, select `Cloudant NoSQL DB` and `Create` the service.
4. Select `Restage` when prompted. IBM Cloud will restart your application and provide the database credentials to your application using the `VCAP_SERVICES` environment variable. This environment variable is only available to the application when it is running on IBM Cloud.

Environment variables enable you to separate deployment settings from your source code. For example, instead of hardcoding a database password, you can store this in an environment variable which you reference in your source code. [Learn more...](/docs/manageapps/depapps.html#app_env)

## 6. Use the database

We're now going to update your local code to point to this database. We'll create a file that will store the credentials for the services the application will use. This file will get used ONLY when the application is running locally. When running in IBM Cloud, the credentials will be read from the VCAP_SERVICES environment variable.

1. Create a file called `.env` in the `get-started-php` directory with the following content:
  ```
  CLOUDANT_HOST=
  CLOUDANT_USERNAME=
  CLOUDANT_PASSWORD=
  ```

2. Back in the IBM Cloud UI, on the Service Details page for your app, click **Service credentials** in the sidebar. Click **New credential** and then **Add**. Open the **View credentials** dropdown to reveal the credentials.

3. Copy and paste values of the `CLOUDANT_HOST`, `CLOUDANT_USERNAME` and `CLOUDANT_PASSWORD` fields into the `.env` file and save the changes.  The result will be something like:
  ```
  CLOUDANT_HOST=abc...yz.cloudant.com
  CLOUDANT_USERNAME=abc...yz
  CLOUDANT_PASSWORD=445d...d1a
  ```

4. Run your application locally.
  ```
php -S localhost:8000
  ```

View your app at: http://localhost:8080. Any names you enter into the app will now get added to the database.

Your local app and  the IBM Cloud app are sharing the database.  View your IBM Cloud app at the URL listed in the output of the push command from above.  Names you add from either app should appear in both when you refresh the browsers.
