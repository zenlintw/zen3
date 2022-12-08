/*****************************************
  PHP compatible JS serializer
  by Dr.Slump, aka Iván Montes
  Sponsored by Cytek NT <www.cyteknt.com>

  based on the toSource extensions v1.0 by Bjørn Rosell www.prototypeDHTML.net

  to serialize the JS variable just do:
  phpserialized = myvar.toPHP();
 *****************************************/

Number.prototype.toPHP=function() {
   if (Math.round(this) == this)
       return 'i:'+this+';'; //integer
   else
       return 'd:'+this+';'; //float
}

String.prototype.toPHP=function() {
   var s = this
   s=s.replace(/\\/g, "\\\\")
   s=s.replace(/\"/g, "\\\"")
   s=s.replace(/\n/g, "\\n")
   s=s.replace(/\r/g, "")
   return 's:'+s.length+':"'+s+'";';
}

Boolean.prototype.toPHP=function() {
   if (variable == true)
       return 'b:1;';
   else
       return 'b:0;';
}

Function.prototype.toPHP=function() {
   return 'N;'; //returns a Null variable for functions
}

Array.prototype.toPHP=function() {
   var a=this
   var s = 'a:'+a.length+':{';

   for (var i=0; i<a.length; i++) {
           s += 'i:'+i+';'+a[i].toPHP();
   }
   s += '}';
   return s
}

Object.prototype.toPHP=function() {
   var o=this

   if (o==null) return 'N;';

   var s='';
   var count=0;
   for (var item in o) {
       if (item=="toPHP") continue;
       count++;

       s += 's:'+item.length+':"'+item+'";';
       if (o[item]==null)
           s +='N;';
       else
           s += o[item].toPHP();
   }
   s = 'a:'+count+':{'+s+'}';

   return s
}
