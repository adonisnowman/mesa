const NodeMediaServer = require('node-media-server');

const config = {
  rtmp: {
    port: 1935,
    chunk_size: 60000,
    gop_cache: true,
    ping: 30,
    ping_timeout: 60
  },
  http: {
    port: 888,
    allow_origin: '*'
  },
  https: {
    port: 8443,
    key: '/etc/letsencrypt/live/swoole.bestaup.com/privkey.pem',
    cert: '/etc/letsencrypt/live/swoole.bestaup.com/privkey.pem',
  },
};

var nms = new NodeMediaServer(config)
nms.run();