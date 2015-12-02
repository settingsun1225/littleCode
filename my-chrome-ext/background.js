// Copyright (c) 2012 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.

// This event is fired each time the user updates the text in the omnibox,
// as long as the extension's keyword mode is still active.
chrome.omnibox.onInputChanged.addListener(
  function(text, suggest) {
    var suggestions = [];
    if (text == 'p') {
      suggestions.push({content: "http://woodpecker.org.cn/abyteofpython_cn/chinese/index.html", description: "python简明教程"});
      suggestions.push({content: "http://learnpythonthehardway.org/book/", description: "learn python the hard way"});
    } else if (text == 's') {
      suggestions.push({content: "http://manual.51yip.com/shell/", description: "shell在线中文手册"});
    } else if (text == 'n') {
      suggestions.push({content: "http://tengine.taobao.org/book/index.html", description: "nginx开发入门"});
    } else if (text == 'fe') {
      suggestions.push({content: "http://www.douban.com/note/330647290/", description: "前端学习指导"});
    } else {
      suggestions.push({content: "", description:"没查到关联页面"});
    }
    chrome.omnibox.setDefaultSuggestion({description:suggestions[0].description});
    suggestions.shift();
    suggest(suggestions);
  });

// This event is fired with the user accepts the input in the omnibox.
chrome.omnibox.onInputEntered.addListener(
  function(text) {
    chrome.tabs.getSelected(null, function(tab)
    {
        var url;
        if (text.substr(0, 7) == 'http://') {
            url = text;
        } else {
            if (text == 'p') {
              url = "http://woodpecker.org.cn/abyteofpython_cn/chinese/index.html";
            } else if (text == 's') {
              url = "http://manual.51yip.com/shell/";
            } else if (text == 'n') {
              url = "http://tengine.taobao.org/book/index.html";
            } else if (text == 'fe') {
              url = "http://www.douban.com/note/330647290/";
            } else {
              url = "http://www.chuanke.com/";
            }
        }
        chrome.tabs.update(tab.id, {url: url});
    });
  }
);
