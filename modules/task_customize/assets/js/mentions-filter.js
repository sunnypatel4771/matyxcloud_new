(function () {
  "use strict";

  // Wait until core function exists
  const wait = setInterval(function () {

    if (typeof window.initializeTinyMceMentions !== "function") {
      return;
    }

    clearInterval(wait);

    // Keep original core function
    const originalInitMentions = window.initializeTinyMceMentions;

    // Override it safely
    window.initializeTinyMceMentions = function (editor, usersCallback) {

      // Call original first
      originalInitMentions(editor, usersCallback);

      // Now PATCH the autocompleter fetch behaviour
      if (!editor || !editor.ui || !editor.perfexCommands) return;

      editor.ui.registry.addAutocompleter('mentions', {
        trigger: '@',
        minChars: 0,
        columns: 1,

        fetch: function (pattern) {
          return new Promise(resolve => {

            editor.perfexCommands.getUsersForMention().then(users => {

              let search = '';

              if (typeof pattern === 'string') {
                search = pattern.toLowerCase();
              } else if (pattern && pattern.term) {
                search = pattern.term.toLowerCase();
              }

              if (!search.length) {
                resolve(users);
                return;
              }

              const filtered = users.filter(user =>
                user.text.toLowerCase().includes(search)
              );

              resolve(filtered);
            });

          });
        },

        onAction: function (autocompleteApi, rng, value) {
          editor.perfexCommands.getUsersForMention().then(users => {
            let user = users.find(user => user.value == value);
            editor.perfexCommands.insertMentionUser(value, user.text, rng);
            autocompleteApi.hide();
          });
        }
      });
    };

  }, 200);

})();
