// Copyright (c) 2012 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.

// This event is fired each time the user updates the text in the omnibox,
// as long as the extension's keyword mode is still active.
chrome.omnibox.onInputChanged.addListener(
  function(text, suggest) {
    var suggestions = [];
    if (text == 'd') {
        suggestions.push({content: "http://www.365rili.com/main/calendar.do", description: "365万年历"});
    } else if (text == 't') {
        suggestions.push({content: "chrome://extensions", description: "chrome扩展工具"});
    } else if (text == 's') {
        suggestions.push({content: "http://explainshell.com/explain", description: "shell解释工具"});
    } else if (text == 'uml') {
        suggestions.push({content: "https://www.websequencediagrams.com/", description: "UML流程图"});
        suggestions.push({content: "https://www.processon.com/", description:"UML图"});
    } else if (text == 'evernote') {
        suggestions.push({content: "https://app.yinxiang.com/", description: "印象笔记"});
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
            if (text == 'd') {
                 url = "http://www.365rili.com/main/calendar.do";
            } else if (text == 't'){
                 url = "chrome://extensions";
            } else if (text == 's') {
                 url = "http://explainshell.com/";
            } else if (text == 'uml') {
                 url = "https://www.websequencediagrams.com/";
            } else if (text == 'evernote') {
                 url = "https://app.yinxiang.com/";
            }
        }
        chrome.tabs.update(tab.id, {url: url});
    });
  }
);
