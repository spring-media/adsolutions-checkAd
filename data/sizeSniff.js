function createHAR(address, title, startTime, resources)
{
    var entries = [];

    resources.forEach(function (resource) {
        var request = resource.request,
            startReply = resource.startReply,
            endReply = resource.endReply;

        if (!request || !startReply || !endReply) {
            return;
        }
        this.scriptSize = 0;
        if (startReply.bodySize && startReply.bodySize !== "-1") {
            wholeSize += startReply.bodySize;
            this.scriptSize += startReply.bodySize;
        }
        if (endReply.bodySize && endReply.bodySize !== "-1") {
            wholeSize += endReply.bodySize;
            this.scriptSize  += endReply.bodySize;
        }

        entries.push({url: request.url, scriptSize: this.scriptSize, duration: resource.duration, start: resource.reqStart, end: resource.reqEnd, subload: resource.subload});
    });

    return { entries: entries };
}

var page = require('webpage').create(),
    system = require('system'),
    wholeSize = 0,
    secure, test = [], error = [];

if (system.args.length === 1) {
    console.log('Usage: sizesniff.js <some URL>');
    phantom.exit(1);
} else {

    page.address = system.args[1];
    page.settings.userAgent = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36';
    page.resources = [];

    page.onError = function(e) {
        error.push(e);
    };

    page.onLoadStarted = function () {
        page.startTime = Date.now();
    };

    page.onResourceRequested = function (req) {
        var subload = page.evaluate(function () {
            return document.readyState === "complete";
        });
        if (!req.url.match("//adtechs.de") && req.url.match('http://')) {
            secure = false;
            //networkRequest.changeUrl(req.url.replace('http://','https://'));
        } else if (!req.url.match("//adtechs.de") && req.url.match('https://') && secure !== false) {
            secure = true;
        }
        page.resources[req.id] = {
            request: req,
            reqStart: Date.now(),
            reqEnd: 0,
            duration: 0,
            startReply: null,
            endReply: null,
            subload: subload
        };
    };

    page.onResourceReceived = function (res) {
        if (res.stage === 'start') {
            page.resources[res.id].startReply = res;
        }
        if (res.stage === 'data') {
            page.resources[res.id].startReply.bodySize += res.bodySize;
        }
        if (res.stage === 'end') {
            page.resources[res.id].endReply = res;
            page.resources[res.id].reqEnd = Date.now();
            page.resources[res.id].duration = ((page.resources[res.id].reqEnd - page.resources[res.id].reqStart) / 1000) + ' s';
        }
    };
    page.frameContent.onResourceRequested = function (req) {
        var subload = page.evaluate(function () {
            return document.readyState === "complete";
        });
        page.resources[req.id] = {
            request: req,
            reqStart: Date.now(),
            reqEnd: 0,
            duration: 0,
            startReply: null,
            endReply: null,
            subload: subload
        };
    };

    page.frameContent.onResourceReceived = function (res) {
        if (res.stage === 'start') {
            page.resources[res.id].startReply = res;
        }
        if (res.stage === 'data') {
            page.resources[res.id].startReply.bodySize += res.bodySize;
        }
        if (res.stage === 'end') {
            page.resources[res.id].endReply = res;
            page.resources[res.id].reqEnd = Date.now();
            page.resources[res.id].duration = ((page.resources[res.id].reqEnd - page.resources[res.id].reqStart) / 1000) + ' s';
        }
    };

    page.open(page.address, function (status) {
        var har;
        if (status !== 'success') {
            console.log('FAIL to load the address ' + page.address + ' with status ' + status);
            phantom.exit(1);
        } else {
            page.endTime = new Date();
            page.title = page.evaluate(function () {
                return document.title;
            });
            page.loadedIn = page.endTime - page.startTime;
            har = createHAR(page.address, page.title, page.startTime, page.resources);
            console.log("<b class='scriptSize'>"+(wholeSize/1024).toFixed(2)+" kB.</b><br/>Die Seite wurde geladen in: <b class='scriptSize'>"+page.loadedIn/1000+" s.</b>");
            console.log(JSON.stringify(har));
            console.log('redirect');
            console.log(secure);
            console.log(JSON.stringify(error));
            console.log(JSON.stringify(test));
            //phantom.exit();
            window.setTimeout(function(){phantom.exit();}, 8000);
        }
    });
}
