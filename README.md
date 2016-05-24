# team-gource
### Gource Visualization for your entire Organization

Gource is a great tool to visualize the history of individual repositories.
This tool will help you to visualize an *entire organization's* activities through multiple repositories combined into a single history. You can configure which repositories should be included, which users should be included, and which users (such as bots) should be excluded in a configuration file. This utility will create a combined history of the given repositories and users; then this combined history can be used with Gource to generate an animation of the team's combined history.

### Prerequisites:
```bash
brew install gource ffmpeg
```

## How to install:
```
composer require ktamas77/team-gource
```

### Configuration example

You need to rename the given `tg.conf.example` file into `tg.conf`, then edit the file.

```yaml
teams:
  - name: main team # verbose name of the team
    collection: frontend-repos # id of the collection of the repos to scan
    members: # list of the members who are part of your team - only these users will be part of the animation
        - name: Tamas # name of the user as it will be see in the rendered animation
          aliases: [ktamas77, Tamas Kalman, ktamas77@gmail.com] # aliases of the user (it will be combined into one)
        - name: John # another user
          aliases: [john123, John Doe, john@mail.com] # user aliases
    excluded: [automated-script, garbage-collector] # excluded these users; put here the bots, if you have any
  - name: team2 # example of another team (you can define as many teams as needed)
    collection: backend-repos
    members:
        - name: Smith
          aliases: [smith123, Smith, smith@mail.com]

collections: # collection of repositories; each connection can be added to a team above
  - name: frontend-repos # id of the collection, this is what we assign for the teams above
    organization: betacorp # name of the organization in github where the repos are belong to
    repos: # list of the repositories
      - name: frontend-website # repository name
      - name: logger # repository name
      - name: monitoring # repository name
  - name: backend-repos # another collection of repos
    organization: unicorp # another organization
    repos: # list of repositories
      - name: logservice # repository name
      - name: userservice # repository name
      - name: microservice # repository name
```

### How to run

After you finished editing the configuration file, simple run `php tg.php`. 
Then run Gource with the generated file.

### Next up

* Optimized / Streamlined Plug-in style data processing
* Integrated Data Visualization Configuration + Gource Render


