# [Headstart Journal](http://headstart.artpi.net/) 🚀 📖

I am currently working on mobile (iOS & Android) version of this project, written in React Native.
Check out [Headstart Journal](http://headstart.artpi.net/) if you are interested.

# Mindfullnes-Journal

This project is inspired by **"5-minute Journal"** ( http://amzn.to/1A62uSa ). Beautiful, very well designed paper journal that uses positive psychology hacks and tricks to force you into beeing a positive, happy and effective individual.

In essence it's a PHP script that creates a note in your **Evernote** account with:
* Daily Mindfullness practice
* Your agenda for the day with TODO items taken from evernote
* Your calendar items for the day
* Your daily habits to practice
* Recap of today's photos

Basically, it's an automated system to make you **AWESOME**.

## Please elaborate

Daily mindfullness practice is widely considered to be one of the best productivity and well-being enhancers possible.

* Counting your blessings is the shortest path to achieve happiness and internal balance. More on that in this TED talk: http://www.ted.com/talks/david_steindl_rast_want_to_be_happy_be_grateful
* Setting your agenda for the day is what **ALL** successfull people do. If you dont want your day to fall to pieces, you better plan it ahead. If you consider *Tim Ferriss* successful, this is what he wants you to do. (https://www.youtube.com/watch?v=7_dUSGfsQZg)
* Doing daily review of your accomplishments during the day is the best way to reflect, draw conclusions and stay awesome.


## What does this do?
I consider  Evernote to be a hub of all my activity. So I keep todo notes as items tagged by *"todo-now"* tag, so all my items with immidiate priority will be found in that tag.

You hook up this script with CRON, so it creates a note with:
* Prepared template (some other evernote note)
* Todo list with:
 * Daily habits you want to practice
 * TODO items  - basically links to notes with a specific tag in Evernote.
 * Agenda for today - items from your *Google Calendar*
*  *Misfit Shine* fitness tracker (http://misfit.com/) stats. I own one, and it's one of my resolutuons to move more, so I want my statisticsin the journal
 * Daily points - I want to make at least 1500 points vie moving with Misfit Shine
 * Sleep statistics - I want to be able to draw conclusions regarding influence of my habits on my sleep patterns. When I have everything in the journal, it's easier to to revise.
* Photos taken from my smartphone this day - I ahave an android phone with cloud backup enabled for google+ photos. So, thumbnails of every photo I take this day gets inserted into journal entry for the day. 

## Setup

I have tied together multiple services. You can skip some of them, commenting out specific portions of code.

### Evernote account
This is a must-have. In your evernote account you will need:
* Evernote developer token - this is a token to connect external services to api. You can get token for your account here: https://www.evernote.com/api/DeveloperToken.action
* Note with template. Every new journal entry is created by copying template note. You can see my template in file TemplateNote/Journal SZABLON.html
* Tag for marking your immidiate todo's. All notes marked by this tag will be an item in your todo for the day.
* Tag for quotes. Random note with this tag gets inserted instead of [CYTAT] in the note.

###Database
You will need mysql database. Everything is mostly needed to save data for google api oauth tokens.

###Google services
* You will need to setup google oauth information in database in order to authorize application with oauth 2.
* Same for google calendar and google+ photos.




