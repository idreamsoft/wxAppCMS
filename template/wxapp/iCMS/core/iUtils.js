function isFunction(obj) {

    // Support: Chrome <=57, Firefox <=52
    // In some browsers, typeof returns "function" for HTML <object> elements
    // (i.e., `typeof document.createElement( "object" ) === "function"`).
    // We don't want to classify *any* DOM node as a function.
    return typeof obj === "function" && typeof obj.nodeType !== "number";
}

function getProto(obj) {
    return obj.getPrototypeOf;
}

function isPlainObject(obj) {
    var proto, Ctor;

    // Detect obvious negatives
    // Use toString instead of jQuery.type to catch host objects
    if (!obj || toString.call(obj) !== "[object Object]") {
        return false;
    }

    proto = getProto(obj);

    // Objects with no prototype (e.g., `Object.create( null )`) are plain
    if (!proto) {
        return true;
    }

    // Objects with prototype are plain iff they were constructed by a global Object function
    Ctor = hasOwn.call(proto, "constructor") && proto.constructor;
    return typeof Ctor === "function" && fnToString.call(Ctor) === ObjectFunctionString;
}

function extend() {
    var options, name, src, copy, copyIsArray, clone,
        target = arguments[0] || {},
        i = 1,
        length = arguments.length,
        deep = false;

    // Handle a deep copy situation
    if (typeof target === "boolean") {
        deep = target;

        // Skip the boolean and the target
        target = arguments[i] || {};
        i++;
    }

    // Handle case when target is a string or something (possible in deep copy)
    if (typeof target !== "object" && !isFunction(target)) {
        target = {};
    }

    // Extend jQuery itself if only one argument is passed
    if (i === length) {
        target = this;
        i--;
    }

    for (; i < length; i++) {

        // Only deal with non-null/undefined values
        if ((options = arguments[i]) != null) {

            // Extend the base object
            for (name in options) {
                src = target[name];
                copy = options[name];

                // Prevent never-ending loop
                if (target === copy) {
                    continue;
                }

                // Recurse if we're merging plain objects or arrays
                if (deep && copy && (isPlainObject(copy) ||
                        (copyIsArray = Array.isArray(copy)))) {

                    if (copyIsArray) {
                        copyIsArray = false;
                        clone = src && Array.isArray(src) ? src : [];

                    } else {
                        clone = src && isPlainObject(src) ? src : {};
                    }

                    // Never move original objects, clone them
                    target[name] = extend(deep, clone, copy);

                    // Don't bring in undefined values
                } else if (copy !== undefined) {
                    target[name] = copy;
                }
            }
        }
    }

    // Return the modified object
    return target;
};

function cmp(x, y) {
    // If both x and y are null or undefined and exactly the same
    if (x === y) {
        return true;
    }

    // If they are not strictly equal, they both need to be Objects
    if (!(x instanceof Object) || !(y instanceof Object)) {
        return false;
    }

    // They must have the exact same prototype chain, the closest we can do is
    // test the constructor.
    if (x.constructor !== y.constructor) {
        return false;
    }

    for (var p in x) {
        // Inherited properties were tested using x.constructor === y.constructor
        if (x.hasOwnProperty(p)) {
            // Allows comparing x[ p ] and y[ p ] when set to undefined
            if (!y.hasOwnProperty(p)) {
                return false;
            }

            // If they have the same strict value or identity then they are equal
            if (x[p] === y[p]) {
                continue;
            }

            // Numbers, Strings, Functions, Booleans must be strictly equal
            if (typeof(x[p]) !== "object") {
                return false;
            }

            // Objects and Arrays must be tested recursively
            if (!Object.equals(x[p], y[p])) {
                return false;
            }
        }
    }

    for (p in y) {
        // allows x[ p ] to be set to undefined
        if (y.hasOwnProperty(p) && !x.hasOwnProperty(p)) {
            return false;
        }
    }
    return true;
};
//用于生成uuid
function S4() {
    return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
}

function uuid(l) {
    if (l) {
        return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
    }
    return (S4() + S4() + S4() + S4());
}

function get_file_md5(path) {
    // let $pieces = path.split(".");
    // return $pieces[2].substring(12,44);
    var rpos = path.lastIndexOf('.')
    return path.slice(0, rpos).slice(-32);
}
//保持纵横比缩放图片，使图片的长边能完全显示出来。也就是说，可以完整地将图片显示出来。
function aspectFit($a) {
    if( $a['w']/$a['h'] > $a['tw']/$a['th']  && $a['w'] >$a['tw'] ){
        $a['h'] = Math.round($a['h'] * ($a['tw']/$a['w']));
        $a['w'] = $a['tw'];
    }else if( $a['w']/$a['h'] <= $a['tw']/$a['th'] && $a['h'] >$a['th']){
        $a['w'] = Math.round($a['w'] * ($a['th']/$a['h']));
        $a['h'] = $a['th'];
    }
    return $a;
}
//宽度不变，高度自动变化，保持原图宽高比不变
function widthFix($a) {
    $a['h'] = Math.round($a['h'] * ($a['tw']/$a['w']));
    $a['w'] = $a['tw'];
    return $a;
}
function metaData($meta,that) {
    that.metaData = {}
    that.hideUI = {};
    if ($meta) {
        var metaArray = $meta;
        for (var mak in metaArray) {
            // console.log(mak);
            let ma = metaArray[mak];
            that.metaData[mak] = ma['value'];
            if (mak.indexOf("hideUI.") !== -1) {
                that.hideUI[ma['name']] = ma['value'] == '1' ? true : false;
            }
        };
        // console.log(that.hideUI, that.metaData);
    }
}

module.exports = {
    uuid,
    cmp,
    extend,
    get_file_md5,aspectFit,widthFix,metaData
}
