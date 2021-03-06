/*
c = new EchoesCrypto();
c.generate_key('sign',true);
c.export_key('sign_public');
c.export_key('sign_private');
c.keychain.sign.exported.public_key
c.keychain.sign.exported.public_key
*/

describe("Parallax", function() {
    var host = ''; // run test from http://localhost/test/test.html only
    var c = new EchoesCrypto();

    var $global = {
        identity: 'jasmine',
        nonce_identity: 'jasmine',
        device: 'dev',
        email: 'jasmine@specrunner.ca',
        privkey:    "-----BEGIN PRIVATE KEY-----\n" +
                    "MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCxGBD38PnNEH2J\n" +
                    "8A4fzo+YpQeMJB0wPokBreUMc6i9Q26ruLXfQWOY0ZtmlYkMqNlJO1GU3IviDzTH\n" +
                    "xIAufvvJph4OomdHrgUKRv93FYJUNoCdAQ7FI0Rf+lgsTYgPynGa0lWjbaRKFWyv\n" +
                    "EKs3TlBzFdrQOH72Zw5BeG4TTHKe2xAMkkxvc5NDNvMrfU2M1hU3HUpZKGGaDaRw\n" +
                    "42GSVbwl6NFzfYlZc5cb0nHSpGAgLjqlL/VIeeYAMpPrRGz9S5lLYnYEnqq8Y8vR\n" +
                    "NneVBGmDKaXAJ9s7Q9JjQGfO86CQxGiTvVAID2BAzRkR3hhpyTyWxXMKyCW5xjQO\n" +
                    "MtVkMSflAgMBAAECggEBAKEFdeBLTobDOLJkACOxiGVNoIgvCg8kvSQ2vi3NOB8m\n" +
                    "ogkngM7HQMzhwT/MNXm6SR9J/UqyIcpg8ye0pqwgL8ZQ8cFyrx/AvbpzhbL8bq0t\n" +
                    "hVG8dXaQM4plMSjPMijRdOxF1TIs7hWiV1jyegtmfMW3mGZ9CAv49kBXRRbtCsce\n" +
                    "esfSrsKW4gddd2O7GcUn9Ls5qTQL+IUnX71DQZBB+XrxtVIHRwZB+fPkIsYmPEep\n" +
                    "+QL7ephZNQX5PnnoTtKaDBk3ou/TFj+sj0NDiiNvLXGLzBcs0h5bNYXsqlwik6r/\n" +
                    "bET/+IigBIIYKFgEaygUlZcA7A6c7Ocko+mY78Zb9OECgYEA1vzdhxKVG7FX/FPt\n" +
                    "Fo2qKMz/aTOpD5R9uNvyeo0Nf3eA/EEIIQ4dI7RA0dUX5PuVPVmL6Zch2A23c93u\n" +
                    "vBnPt7dQ1XvzwL/5Ih9wyFBLYllSzNFM35rwJ6WdY3SDk8UkrDWnKrHl9JgMsRAo\n" +
                    "Ak7yTRnKsGxrfB5HFyEkBqhFux0CgYEA0uCfjM7MdroJjW6lc9vCkiK2xF5BsMNS\n" +
                    "N0SFvuSVELK9Uu6agQMAAzDuRHPKRjLE8+igaKBp9YGErpzw8/fdehJKDSQ2Qe2X\n" +
                    "dns/PJNiBiRkCStxkGdQ9OY+HzsB+wdRXGqufve9ZzvViXFDwyC/ciSPNQ0nboOr\n" +
                    "Bex3M3jPvWkCgYB6CnNq4OaaINM0nyPggKzOmoiG3Neky4OO8/SzEu1NpbYn/Haz\n" +
                    "5QIvqXx+YTw+NX7jo7ij8rI4ppi0gpCqYPCkdsDBX6rgiVnQwA5S+BnDC7fQIyRD\n" +
                    "bv3TU9WDhrnGocwOoipyNyi177aMsQI1RUGQ+Qoxmmptu/ZZL+v2h4GGFQKBgDrm\n" +
                    "VIoexMswoTRowDvBGPJ6UbQ9Y3m8xHz9XaH49I3Kbsj4Lh10ug8qHpLuCIRc79f0\n" +
                    "lMFEdPQGrgmbek2aYLkU3KwY/jKh6xlDyWDhBZMTnQFWqgycXudYW+ebMq1r2NTI\n" +
                    "U17RUYzBEJ6oDTY2MmLuRTWbK/VZP26TIqQ0zUlpAoGAb15Mtm5wf0J7HNv+o2QY\n" +
                    "J7HwSHwg5wJ5xMPoNbNUnGwFLHcqJ5iQhpikO6pUyLEMEUf05B43SxuQkEUj8tKO\n" +
                    "BVwKqjRYtB5vIbzFC55O7RD23dwfcSKWvzphLsJRJXTgatg+9lYI0QJEiqa0SDeR\n" +
                    "5U178ThLhpQFRhu4ZZKXUvk=\n" +
                    "-----END PRIVATE KEY-----\n",
        pubkey:     "-----BEGIN PUBLIC KEY-----\n" +
                    "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsRgQ9/D5zRB9ifAOH86P\n" +
                    "mKUHjCQdMD6JAa3lDHOovUNuq7i130FjmNGbZpWJDKjZSTtRlNyL4g80x8SALn77\n" +
                    "yaYeDqJnR64FCkb/dxWCVDaAnQEOxSNEX/pYLE2ID8pxmtJVo22kShVsrxCrN05Q\n" +
                    "cxXa0Dh+9mcOQXhuE0xyntsQDJJMb3OTQzbzK31NjNYVNx1KWShhmg2kcONhklW8\n" +
                    "JejRc32JWXOXG9Jx0qRgIC46pS/1SHnmADKT60Rs/UuZS2J2BJ6qvGPL0TZ3lQRp\n" +
                    "gymlwCfbO0PSY0BnzvOgkMRok71QCA9gQM0ZEd4Yack8lsVzCsglucY0DjLVZDEn\n" +
                    "5QIDAQAB\n" +
                    "-----END PUBLIC KEY-----\n",

        nonce: 'empty',
        nonce_signature: 'empty',
        session_id: 'empty',

        session_ip: '127.0.0.1',

    }

    describe("register", function() {
        var o = new EchoesObject('register');

        var test_register = function(test_name, data, expected) {
            it("'" + test_name + "' should return " + expected, function(done) {
                var resolved = function(r) {
                    o.log('(' + test_name + ') resolved: ' + JSON.stringify(r));
                    expect(r.identity).toEqual(data.identity);
                    expect(r.session_ip).toEqual(data.session_ip);
                    expect(r.status).toEqual(expected);
                };
                var rejected = function(e) {
                    o.log('rejected: ' + e.statusText + ':' + e.status, 3);
                    expect(e).toBeUndefined();
                    done();
                };


                for (var item in data) {
                    if (data[item] == 'use global') {
                        data[item] = $global[item];
                    }
                }


                $.ajax({
                    type: "POST",
                    url: host + '/register/',
                    data: data,
                    dataType: 'json',
                })
                .done(function (data) { resolved(data); })
                .fail(function (err) { rejected(err); })
                .always(function() { done() });

            });
        }

        var mock_register = {
            'bad_id': {
                data: { identity: '', pubkey: 'use global', device: 'use global', email: 'use global' },
                expected: 'error',
            },
            'bad_device': {
                data: { identity: 'use global', pubkey: 'use global', device: '!', email: 'use global' },
                expected: 'error',
            },
            'bad_email': {
                data: { identity: 'use global', pubkey: 'use global', device: 'use global', email: 'uwotm8@' },
                expected: 'error',
            },
            'bad_pubkey': {
                data: { identity: 'use global', pubkey: 'xx', device: 'use global', email: 'use global' },
                expected: 'error',
            },
            'good_id': {
                data: { identity: 'use global', pubkey: 'use global', device: 'use global', email: 'use global' },
                expected: 'success',
            }
        }
        for (var t in mock_register) {
            test_register(t, mock_register[t].data, mock_register[t].expected);
        }
    });

    describe("auth-request", function() {
        var o = new EchoesObject('auth-request');

        var test_auth_request = function(test_name, data, expected) {
            it("'" + test_name + "' should return " + expected, function(done) {
                var resolved = function(r) {
                    o.log('(' + test_name + ') resolved: ' + JSON.stringify(r));
                    expect(r.status).toEqual(expected);

                    if (typeof r.nonce != 'undefined') {
                        $global.nonce = r.nonce;
                    }
                };
                var rejected = function(e) {
                    o.log('rejected: ' + e.statusText + ':' + e.status, 3);
                    expect(e).toBeUndefined();
                    done();
                };

                $.ajax({
                    type: "POST",
                    url: host + '/auth-request/',
                    data: data,
                    dataType: 'json',
                })
                .done(function (data) { resolved(data); })
                .fail(function (err) { rejected(err); })
                .always(function() { done() });

            });
        }

        var mock_auth_request = {
            'bad_id': {
                data: { identity: '$#%', device: $global.device },
                expected: 'error',
            },
            'bad_device': {
                data: { identity: $global.identity, device: '$%$#' },
                expected: 'error',
            },
            'good_id': {
                data: { identity: $global.identity, device: $global.device },
                expected: 'success',
            }
        }
        for (var t in mock_auth_request) {
            test_auth_request(t, mock_auth_request[t].data, mock_auth_request[t].expected);
        }

    });

    describe("auth-reply", function() {
        var o = new EchoesObject('auth-reply');

        it("should import privkey to 'sign' keychain", function(done) {
            var resolved = function(r) {
                expect(c.keychain['sign'].imported.private_key).not.toBe(null);
                done();
            };
            var rejected = function(e) {
                expect(e).toBeUndefined();
                done();
            };

            c.import_key('sign', $global.privkey, 'pkcs8', true)
                .then(resolved)
                .catch(rejected);
        });
        it("should sign $global.nonce with imported 'sign' privkey", function(done) {
            var resolved = function(r) {
                $global.nonce_signature = btoa(c.resulting_signature);
                expect(c.resulting_signature).not.toBe(null);
                done();
            };
            var rejected = function(e) {
                expect(e).toBeUndefined();
                done();
            };

            c.sign($global.nonce, c.keychain['sign'].imported.private_key)
                .then(resolved)
                .catch(rejected);
        });

        var test_auth_reply = function(test_name, data, expected) {
            it("'" + test_name + "' should return " + expected, function(done) {
                var resolved = function(r) {
                    o.log('(' + test_name + ') resolved: ' + JSON.stringify(r));
                    expect(r.status).toEqual(expected);

                    if (r.status == 'success'
                        && typeof r.session_id != 'undefined'
                        && r.session_id.length > 1) {
                        $global.session_id = r.session_id;
                    }
                };
                var rejected = function(e) {
                    o.log('rejected: ' + e.statusText + ':' + e.status, 3);
                    expect(e).toBeUndefined();
                    done();
                };

                for (var item in data) {
                    if (data[item] == 'use global') {
                        data[item] = $global[item];
                    }
                }

                $.ajax({
                    type: "POST",
                    url: host + '/auth-reply/',
                    data: data,
                    dataType: 'json',
                })
                .done(function (data) { resolved(data); })
                .fail(function (err) { rejected(err); })
                .always(function() { done() });

            });
        }

        var mock_auth_reply = {
            'bad_sig': {
                data: {
                    nonce_identity: 'use global',
                    nonce: 'use global',
                    nonce_signature: 'invalid!',
                    device: 'use global',
                },
                expected: 'error',
            },
            'wrong_id': {
                data: {
                    nonce_identity: 'test',
                    nonce: 'use global',
                    nonce_signature: 'use global',
                    device: 'use global',
                },
                expected: 'error',
            },
            'good_auth': {
                data: {
                    nonce_identity: 'use global',
                    nonce: 'use global',
                    nonce_signature: 'use global',
                    device: 'use global',
                },
                expected: 'success',
            },
        }
        for (var t in mock_auth_reply) {
            test_auth_reply(t, mock_auth_reply[t].data, mock_auth_reply[t].expected);
        }
    });

    describe("verify-session", function() {
        var o = new EchoesObject('verify-session');

        var test_verify_session = function(test_name, data, expected) {
            it("'" + test_name + "' should return " + expected, function(done) {
                var resolved = function(r) {
                    o.log('(' + test_name + ') resolved: ' + JSON.stringify(r));
                    expect(r.session_id).toEqual(data.session_id);
                    expect(r.status).toEqual(expected);
                };
                var rejected = function(e) {
                    o.log('rejected: ' + e.statusText + ':' + e.status, 3);
                    expect(e).toBeUndefined();
                    done();
                };


                for (var item in data) {
                    if (data[item] == 'use global') {
                        data[item] = $global[item];
                    }
                }


                $.ajax({
                    type: "POST",
                    url: host + '/verify-session/',
                    data: data,
                    dataType: 'json',
                })
                .done(function (data) { resolved(data); })
                .fail(function (err) { rejected(err); })
                .always(function() { done() });

            });
        }

        var mock_verify_session = {
            'bad_sid': {
                data: { session_id: 'bad_sid', session_ip: 'use global', identity: 'use global' },
                expected: 'error',
            },
            'bad_ip': {
                data: { session_id: 'use global', session_ip: '127.0.0.2', identity: 'use global' },
                expected: 'error',
            },
            'wrong_id': {
                data: { session_id: 'use global', session_ip: null, identity: 'wrongid' },
                expected: 'error',
            },
            'good_session': {
                data: { session_id: 'use global', session_ip: null, identity: 'use global' },
                expected: 'success',
            }
        }
        for (var t in mock_verify_session) {
            test_verify_session(t, mock_verify_session[t].data, mock_verify_session[t].expected);
        }
    });

    describe("delete-identity", function() {
        var o = new EchoesObject('delete-identity');

        var test_delete_identity = function(test_name, data, expected) {
            it("'" + test_name + "' should return " + expected, function(done) {
                var resolved = function(r) {
                    o.log('(' + test_name + ') resolved: ' + JSON.stringify(r));
                    expect(r.identity).toEqual(data.identity);
                    expect(r.session_ip).toEqual(data.session_ip);
                    expect(r.status).toEqual(expected);
                };
                var rejected = function(e) {
                    o.log('rejected: ' + e.statusText + ':' + e.status, 3);
                    expect(e).toBeUndefined();
                    done();
                };


                for (var item in data) {
                    if (data[item] == 'use global') {
                        data[item] = $global[item];
                    }
                }


                $.ajax({
                    type: "POST",
                    url: host + '/delete-identity/',
                    data: data,
                    dataType: 'json',
                })
                .done(function (data) { resolved(data); })
                .fail(function (err) { rejected(err); })
                .always(function() { done() });

            });
        }

        var mock_delete_identity = {
            'no_confirmation': {
                data: { identity: '' },
                expected: 'error',
            },
            'bad_confirmation': {
                data: { identity: 'uwotm8' },
                expected: 'error',
            },
            'good_delete': {
                data: { identity: 'use global' },
                expected: 'success',
            }
        }
        for (var t in mock_delete_identity) {
            test_delete_identity(t, mock_delete_identity[t].data, mock_delete_identity[t].expected);
        }
    });
});
