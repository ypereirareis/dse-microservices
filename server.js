var http = require('http');
var sys = require('sys')
var exec = require('child_process').exec;
var express = require('express')
var app = express()


// Add headers
app.use(function (req, res, next) {
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');
    res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');
    res.setHeader('Access-Control-Allow-Credentials', true);
    next();
});

app.get('/', function (req, res) {
  res.type('application/json');
  exec("bin/database-schema-explorer", function (error, stdout, stderr) {
    res.end(stdout);
  });
});

app.get('/tables', function (req, res) {
  res.type('application/json');
  exec("bin/database-schema-explorer database:list-tables -c locale -f json -s", function (error, stdout, stderr) {
    res.end(stdout);
  });
});

app.get('/execute/:query', function (req, res) {
  res.type('application/json');
  exec("bin/database-schema-explorer database:execute-query -c locale '"+ req.params.query +"'", function (error, stdout, stderr) {
    res.end(stdout);
  });
});

app.get('/tables/:table', function (req, res) {
  res.type('application/json');
  exec("bin/database-schema-explorer database:detail-table "+req.params.table+" -c locale -f json", function (error, stdout, stderr) {
    res.end(stdout);
  });
});

var server = app.listen(3000, function () {
  var host = server.address().address
  var port = server.address().port
  console.log('Example app listening at http://%s:%s', host, port)
});

