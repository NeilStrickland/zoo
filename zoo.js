var zoo = Object.create(frog);

zoo.object = Object.create(frog.object);

zoo.object.ajax_url = location.hostname + '/zoo/';
